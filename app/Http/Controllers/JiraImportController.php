<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessJiraImportJob;
use App\Models\Integration;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\TestSuite;
use App\Models\Story as ArxitestStory;
use App\Models\TestCase as ArxitestTestCase;
use App\Services\JiraService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Team;
use App\Models\TestScript;
use App\Models\TestCase;
use Illuminate\Support\Facades\Http;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class JiraImportController extends Controller
{
    use JsonResponse;
    /**
     * Show options for importing from Jira.
     */
    public function showImportOptions(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $teamId = $team->id;

        $jiraConnected = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $teamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->exists();

        if (! $jiraConnected) {
            return redirect()->route('dashboard.integrations.index')->with('error', 'Please connect Jira before importing.');
        }

        $team = Team::findOrFail($teamId);
        try {
            $jiraService    = new JiraService($teamId);
            $jiraProjects   = $jiraService->getProjects();
        } catch (\Exception $e) {
            Log::error('Error fetching Jira projects for import options', ['error' => $e->getMessage(), 'team_id' => $teamId]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Could not access Jira projects: ' . $e->getMessage());
        }

        $existingProjects = $team->projects()->get(['id', 'name']);

        return view('dashboard.integrations.jira-import-options', compact('jiraProjects', 'existingProjects', 'team'));
    }

    /**
     * Import a Jira project, creating or using an Arxitest project.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function importProject(Request $request)
{
    Log::info('Jira import request received', [
        'is_ajax' => $request->ajax(),
        'wants_json' => $request->wantsJson(),
        'accept' => $request->header('Accept'),
        'data' => $request->all()
    ]);

    // Fix the validator to handle checkboxes correctly
    $validator = Validator::make($request->all(), [
        'jira_project_key'       => 'required|string|max:100',
        'jira_project_name'      => 'required|string|max:255',
        'create_new_project'     => 'required|in:0,1',
        'arxitest_project_id' => $request->input('create_new_project') == '0'
            ? 'required|uuid|exists:projects,id'
            : 'nullable',
        'new_project_name'       => 'required_if:create_new_project,1|string|max:255',
        // Handle checkboxes properly - they only exist when checked
        'import_epics'           => 'sometimes',
        'import_stories'         => 'sometimes',
        'generate_test_scripts'  => 'sometimes',
        'jql_filter'             => 'nullable|string|max:1000',
        'max_issues'             => 'nullable|integer|min:0',
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed', [
            'errors' => $validator->errors()->toArray(),
            'data' => $request->all()
        ]);

        // Always return JSON for AJAX requests
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()->toArray()
        ], 422);
    }

    // Get validated data
    $validated = $validator->validated();
    Log::info('Validation successful', $validated);

    $teamId = session('current_team');
    if (!$teamId) {
        return response()->json([
            'success' => false,
            'message' => 'Team selection required.'
        ], 400);
    }

    try {
        // Create new project or use existing one
        if ($request->input('create_new_project') == '1') {
            $name = $validated['new_project_name'];
            $project = Project::create([
                'name'        => $name,
                'description' => "Imported from Jira: " . $validated['jira_project_name'],
                'team_id'     => $teamId,
                'settings'    => [
                    'jira_import' => [
                        'source' => $validated['jira_project_key'],
                        'date' => now()->toDateTimeString()
                    ]
                ],
            ]);
            Log::info('Created new project for Jira import', [
                'project_id' => $project->id,
                'jira_key' => $validated['jira_project_key']
            ]);
        } else {
            $project = Project::findOrFail($validated['arxitest_project_id']);
            if ($project->team_id !== $teamId) {
                throw new \Exception('No permission for that project.');
            }
        }

        // Initialize progress tracking
        $this->initializeImportProgress($project->id);

        // Get Jira service for the team
        $jiraService = new JiraService($teamId);

        // Build JQL query
        $jqlParts = ['project = "' . str_replace('"', '\"', $validated['jira_project_key']) . '"'];
        $types = [];

        // Correctly check for checkbox values
        if ($request->has('import_epics')) $types[] = 'Epic';
        if ($request->has('import_stories')) $types = array_merge($types, ['Story', 'Task', 'Bug']);

        if ($types) {
            $jqlParts[] = 'issueType IN ("' . implode('","', $types) . '")';
        }

        if (!empty($validated['jql_filter'])) {
            $jqlParts[] = '(' . $validated['jql_filter'] . ')';
        }

        $jql = implode(' AND ', $jqlParts) . ' ORDER BY created DESC';

        // Define fields to retrieve
        $fields = [
            'summary',
            'description',
            'issuetype',
            'parent',
            'status',
            'created',
            'updated',
            'labels',
            'priority',
            'assignee',
            'components',
            'customfield_10005' // Common field for acceptance criteria
        ];

        // Fetch issues from Jira
        $issues = $jiraService->getIssuesWithJql($jql, $fields, $validated['max_issues'] ?? 50);

        // Queue the actual import process as a job to prevent timeout
        Log::info('Attempting to dispatch job', [
            'project_id' => $project->id,
            'issues_count' => count($issues)
        ]);

        dispatch(new ProcessJiraImportJob(
            $project->id,
            $issues,
            [
                'importEpics' => $request->has('import_epics'),
                'importStories' => $request->has('import_stories'),
                'generateTestScripts' => $request->has('generate_test_scripts'),
                'userId' => Auth::id(),
            ]
        ));

        Log::info('Job dispatched successfully');

        // Always return JSON
        return response()->json([
            'success' => true,
            'data' => [
                'project_id' => $project->id,
                'message' => 'Jira import initiated. Check progress for updates.'
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Jira import failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if (isset($project)) {
            $this->setImportCompleted($project->id, false, null, $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'Import failed: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * API endpoint that always returns JSON progress info
     */
    public function getImportProgressJson(Request $request, ?string $progressId = null)
    {
        // Same logic as checkImportProgress but force JSON
        $progressId = $progressId ?? $request->input('progress_id');

        if (!$progressId) {
            return response()->json([
                'success' => false,
                'message' => 'No progress ID provided',
                'is_complete' => true, // Force complete to stop polling
            ], 400);
        }

        $progressData = Cache::get("progress_{$progressId}");

        if (!$progressData) {
            return response()->json([
                'success' => false,
                'message' => 'Progress data not found',
                'is_complete' => true, // Force complete to stop polling
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $progressData
        ]);
    }

    /**
     * Get the current progress of a Jira import and display the progress view
     *
     * @param Request $request
     * @param string|null $projectId Optional project ID to check progress for
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function getImportProgress(Request $request, ?string $projectId = null)
    {
        try {
            // If no project ID is provided in the URL, check if it's in the request
            $projectId = $projectId ?: $request->query('project_id');

            Log::info('Import progress request received', [
                'project_id' => $projectId,
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson(),
                'accept' => $request->header('Accept')
            ]);

            if (!$projectId) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No project ID provided'
                    ], 400);
                }

                // Return error view for non-AJAX requests
                return redirect()->route('dashboard.integrations.index')
                    ->with('error', 'No project ID provided');
            }

            // Get progress from cache
            $progress = cache()->get("jira_import_progress_{$projectId}", []);

            // Calculate elapsed time if applicable
            if (!empty($progress['start_time'])) {
                $startTime = $progress['start_time'];
                $endTime = $progress['end_time'] ?? now()->timestamp;
                $progress['elapsed_seconds'] = $endTime - $startTime;
                $progress['elapsed_time'] = $this->formatElapsedTime($progress['elapsed_seconds']);
            }

            // Always return JSON for AJAX/fetch requests
            if ($request->ajax() || $request->expectsJson() || $request->header('Accept') == 'application/json') {
                return response()->json([
                    'success' => true,
                    'data' => ['progress' => $progress]
                ]);
            }

            // Return view for regular requests
            return view('dashboard.integrations.jira-import-progress', [
                'project_id' => $projectId
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking import progress', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check progress: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Error checking progress: ' . $e->getMessage());
        }
    }

    /**
     * Format elapsed seconds into a human-readable string
     *
     * @param int $seconds
     * @return string
     */
    private function formatElapsedTime(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} seconds";
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return "{$minutes}m {$remainingSeconds}s";
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m {$remainingSeconds}s";
    }

    protected function initializeImportProgress(string $projectId): void
    {
        cache()->put("jira_import_progress_{$projectId}", [
            'epics' => 0,
            'stories' => 0,
            'testCases' => 0,
            'testScripts' => 0,
            'completed' => false,
            'success' => null,
            'error' => null,
            'start_time' => now()->timestamp
        ], 3600);
    }

    /**
     * Update a specific count in the import progress
     *
     * @param string $projectId
     * @param string $key The counter to increment (epics, stories, etc.)
     * @param int $inc Amount to increment by
     * @return void
     */
    protected function updateImportProgress(string $projectId, string $key, int $inc = 1): void
    {
        $keyFull = "jira_import_progress_{$projectId}";
        $prog = cache()->get($keyFull, []);
        $prog[$key] = ($prog[$key] ?? 0) + $inc;
        cache()->put($keyFull, $prog, 3600);
    }

    /**
     * Mark an import as completed
     *
     * @param string $projectId
     * @param bool $success
     * @param array|null $stats
     * @param string|null $error
     * @param array|null $scriptStatus
     * @return void
     */
    protected function setImportCompleted(string $projectId, bool $success, $stats = null, ?string $error = null, ?array $scriptStatus = null): void
    {
        $keyFull = "jira_import_progress_{$projectId}";
        $prog = cache()->get($keyFull, []);
        $prog['completed'] = true;
        $prog['success'] = $success;
        if ($error !== null) $prog['error'] = $error;
        if ($stats !== null) $prog['stats'] = $stats;
        if ($scriptStatus !== null) $prog['script_generation'] = $scriptStatus;
        $prog['end_time'] = now()->timestamp;
        cache()->put($keyFull, $prog, 3600);
    }

    public function extractPlainTextFromAdf(array $doc): string
    {
        $text = '';
        array_walk_recursive($doc, function ($value, $key) use (&$text) {
            // Whenever we see a "text" key, grab its value
            if ($key === 'text') {
                $text .= $value;
            }
        });
        return $text;
    }

    /**
     * Generate a script for a test case using AI
     *
     * @param TestCase $testCase The test case to generate a script for
     * @param string $framework The framework to use (selenium-python, cypress, etc)
     * @return string The generated script content
     */
    private function generateScriptForTestCase(TestCase $testCase, string $framework): string
    {
        // Debug log the API key configuration (masked for security)
        $apiKey = env('OPENAI_API_KEY', config('services.openai.key'));
        Log::debug('OpenAI API configuration check', [
            'api_key_configured' => !empty($apiKey),
            'api_key_preview' => !empty($apiKey) ? substr($apiKey, 0, 3) . '...' . substr($apiKey, -3) : 'not set'
        ]);
        // Prepare the prompt for the AI
        $prompt = "Generate a test script for the following test case:\n\n";
        $prompt .= "Title: {$testCase->title}\n";
        $prompt .= "Steps:\n" . implode("\n", $testCase->steps) . "\n";
        $prompt .= "Expected Results: {$testCase->expected_results}\n";

        // Call the OpenAI API
        $apiKey = env('OPENAI_API_KEY', config('services.openai.key'));
        $model = env('OPENAI_MODEL', config('services.openai.model', 'gpt-4o'));

        Log::info('Generating test script via AI', [
            'test_case_id' => $testCase->id,
            'framework' => $framework
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->getScriptGenerationSystemPrompt($framework)],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            Log::error('AI script generation failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("AI generation failed: " . $response->status());
        }

        $scriptContent = $response->json('choices.0.message.content');

        // Clean up any possible markdown code blocks
        $scriptContent = trim(preg_replace('/^```[\w]*\n|```$/m', '', $scriptContent));

        return $scriptContent;
    }

    /**
     * Get the system prompt for script generation
     */
    private function getScriptGenerationSystemPrompt(string $framework): string
    {
        return <<<PROMPT
You are an AI assistant that specializes in generating test automation scripts. You'll create a test script based on the provided test case information.

Create a complete, working test script using the {$framework} framework. Do not abbreviate or omit parts of the code - generate a complete test script that could be executed.

Follow these specific guidelines:
1. For selenium-python:
   - Use Selenium WebDriver with Python
   - Include proper imports, setup, teardown
   - Use best practices like explicit waits
   - Structure as a proper Python test using unittest or pytest

2. For cypress:
   - Use Cypress JavaScript syntax
   - Include proper imports, before/after hooks
   - Follow Cypress best practices
   - Structure as a proper Cypress test

Your response should be ONLY the code for the test script, without explanations, markdown formatting, or code block markers.
PROMPT;
    }
    /**
     * Process the import of Jira issues into Arxitest entities
     *
     * @param array $issues Jira issues to import
     * @param Project $project Arxitest project to import into
     * @param array $options Import options
     * @return array Import statistics
     */
    private function processJiraImport(array $issues, Project $project, array $options): array
    {
        $importEpics = $options['importEpics'] ?? true;
        $importStories = $options['importStories'] ?? true;
        $generateTestScripts = $options['generateTestScripts'] ?? false;

        // Track import progress
        $epicToSuiteMap = [];
        $epicCount = 0;
        $storyCount = 0;
        $testCaseCount = 0;
        $testScriptCount = 0;
        $defaultSuite = null;

        // Create a common timestamp for this import batch
        $importBatchId = Str::uuid()->toString();
        $importTimestamp = now();

        // Process Epics -> Test Suites (only if importing epics)
        if ($importEpics) {
            foreach ($issues as $issue) {
                if (Arr::get($issue, 'fields.issuetype.name') === 'Epic') {
                    $epicName = Arr::get($issue, 'fields.summary', 'Untitled Epic ' . $issue['key']);
                    $rawDesc = Arr::get($issue, 'fields.description', []);
                    $epicDescription = is_array($rawDesc)
                        ? $this->extractPlainTextFromAdf($rawDesc)
                        : $rawDesc;

                    // Enhanced metadata
                    $suiteSettings = [
                        'jira_epic_id' => $issue['id'],
                        'jira_epic_key' => $issue['key'],
                        'import_batch_id' => $importBatchId,
                        'import_timestamp' => $importTimestamp,
                        'jira_status' => Arr::get($issue, 'fields.status.name'),
                        'jira_labels' => Arr::get($issue, 'fields.labels', []),
                        'jira_priority' => Arr::get($issue, 'fields.priority.name'),
                    ];

                    // Create or update the test suite
                    $testSuite = TestSuite::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'settings->jira_epic_key' => $issue['key']
                        ],
                        [
                            'name' => $epicName,
                            'description' => $epicDescription ?? 'From Epic: ' . $issue['key'],
                            'settings' => $suiteSettings
                        ]
                    );

                    $epicToSuiteMap[$issue['id']] = $testSuite->id;
                    $epicCount++;

                    // Update progress
                    $this->updateImportProgress($project->id, 'epics', 1);
                }
            }
        }

        // Process Stories/Tasks/Bugs -> Test Cases (only if importing stories)
        if ($importStories) {
            foreach ($issues as $issue) {
                $issueType = Arr::get($issue, 'fields.issuetype.name');
                // Skip epics and unknown issue types
                if ($issueType === 'Epic' || !in_array($issueType, ['Story', 'Task', 'Bug'])) {
                    continue;
                }

                $storyTitle = Arr::get($issue, 'fields.summary', 'Untitled ' . $issueType . ' ' . $issue['key']);
                $rawDesc = Arr::get($issue, 'fields.description', []);
                $storyDescription = is_array($rawDesc)
                    ? $this->extractPlainTextFromAdf($rawDesc)
                    : $rawDesc;
                $parentEpicId = Arr::get($issue, 'fields.parent.id');
                $acceptanceCriteria = Arr::get($issue, 'fields.customfield_10005', ''); // Common field for AC

                // Extract acceptance criteria
                $criteria = $this->extractAcceptanceCriteria($acceptanceCriteria, $storyDescription);

                // Determine suite to associate with
                $suiteId = null;
                if ($parentEpicId && isset($epicToSuiteMap[$parentEpicId])) {
                    $suiteId = $epicToSuiteMap[$parentEpicId];
                } else {
                    // Create default suite if needed
                    if (!$defaultSuite) {
                        $defaultSuite = TestSuite::firstOrCreate(
                            [
                                'project_id' => $project->id,
                                'name' => 'Imported Issues (Uncategorized)'
                            ],
                            [
                                'description' => 'Issues without epics imported from Jira.',
                                'settings' => [
                                    'import_batch_id' => $importBatchId,
                                    'import_timestamp' => $importTimestamp
                                ]
                            ]
                        );
                    }
                    $suiteId = $defaultSuite->id;
                }

                // Enhanced metadata for stories
                $storyMetadata = [
                    'jira_id' => $issue['id'],
                    'jira_key' => $issue['key'],
                    'jira_issue_type' => $issueType,
                    'jira_status' => Arr::get($issue, 'fields.status.name'),
                    'jira_priority' => Arr::get($issue, 'fields.priority.name'),
                    'jira_labels' => Arr::get($issue, 'fields.labels', []),
                    'jira_assignee' => Arr::get($issue, 'fields.assignee.displayName'),
                    'jira_components' => collect(Arr::get($issue, 'fields.components', []))
                        ->pluck('name')
                        ->toArray(),
                    'import_batch_id' => $importBatchId,
                    'import_timestamp' => $importTimestamp
                ];

                // Create the Arxitest Story
                $arxitestStory = ArxitestStory::updateOrCreate(
                    [
                        'external_id' => $issue['key'],
                        'source' => 'jira'
                    ],
                    [
                        'title' => $storyTitle,
                        'description' => $storyDescription,
                        'metadata' => $storyMetadata
                    ]
                );

                $storyCount++;
                $this->updateImportProgress($project->id, 'stories', 1);

                // Generate test steps
                $testSteps = $this->generateTestSteps($criteria, $storyTitle, $issueType);

                // Create test case
                $testCase = ArxitestTestCase::firstOrCreate(
                    [
                        'suite_id' => $suiteId,
                        'title' => "Verify: " . Str::limit($storyTitle, 90)
                    ],
                    [
                        'story_id' => $arxitestStory->id,
                        'steps' => $testSteps,
                        'expected_results' => $this->generateExpectedResults($criteria, $storyTitle, $issueType),
                        'priority' => $this->mapJiraPriorityToArxitest(
                            Arr::get($issue, 'fields.priority.name', 'Medium')
                        ),
                        'status' => 'draft',
                        'tags' => array_merge(
                            ['jira-import', $issue['key'], strtolower($issueType)],
                            Arr::get($issue, 'fields.labels', [])
                        )
                    ]
                );

                $testCaseCount++;
                $this->updateImportProgress($project->id, 'testCases', 1);

                // Generate test scripts if requested - this would be implemented in a real app
                // For now we just update the progress count
                // In the processJiraImport method, replace the placeholder with:
                if ($generateTestScripts) {
                    // For each test case, generate a script using the AI
                    try {
                        $framework = $project->settings['default_framework'] ?? 'selenium-python';

                        // Call the script generation service
                        $scriptContent = $this->generateScriptForTestCase($testCase, $framework);

                        // Create test script record
                        $testScript = new TestScript();
                        $testScript->test_case_id = $testCase->id;
                        $testScript->creator_id = Auth::id();
                        $testScript->name = "{$testCase->title} - {$framework} Script";
                        $testScript->framework_type = $framework;
                        $testScript->script_content = $scriptContent;
                        $testScript->metadata = [
                            'created_through' => 'ai',
                            'source' => 'jira-import',
                            'import_batch_id' => $importBatchId
                        ];
                        $testScript->save();

                        $testScriptCount++;
                        $this->updateImportProgress($project->id, 'testScripts', 1);
                    } catch (\Exception $e) {
                        // Log error but continue with other test cases
                        Log::error("Failed to generate script for test case {$testCase->id}", [
                            'error' => $e->getMessage(),
                            'test_case' => $testCase->id
                        ]);
                    }
                }
            }
        }

        if ($generateTestScripts) {
            try {
                // Determine the framework to use
                $framework = $project->settings['default_framework'] ?? 'selenium-python';

                // Check if OpenAI API key is configured
                $apiKey = env('OPENAI_API_KEY', config('services.openai.key'));
                if (empty($apiKey)) {
                    throw new \Exception("OpenAI API key not configured. Please set OPENAI_API_KEY in your environment.");
                }

                // Generate script for this test case
                $scriptContent = $this->generateScriptForTestCase($testCase, $framework);

                // Create the test script record
                $testScript = new TestScript();
                $testScript->test_case_id = $testCase->id;
                $testScript->creator_id = Auth::id();
                $testScript->name = "{$testCase->title} - {$framework} Script";
                $testScript->framework_type = $framework;
                $testScript->script_content = $scriptContent;
                $testScript->metadata = [
                    'created_through' => 'ai',
                    'source' => 'jira-import',
                    'import_batch_id' => $importBatchId,
                    'import_timestamp' => $importTimestamp
                ];
                $testScript->save();

                $testScriptCount++;
                $this->updateImportProgress($project->id, 'testScripts', 1);

                Log::info('Generated test script for test case', [
                    'test_case_id' => $testCase->id,
                    'test_script_id' => $testScript->id,
                    'framework' => $framework
                ]);
            } catch (\Exception $e) {
                // Log error but continue with other test cases
                Log::error("Failed to generate script for test case {$testCase->id}", [
                    'error' => $e->getMessage(),
                    'test_case' => $testCase->id
                ]);

                // Track error count in session for summary
                $errorCount = session('script_generation_errors', 0);
                session(['script_generation_errors' => $errorCount + 1]);
            }
        }

        Log::info('Import completed with actual counts', [
            'epics' => $epicCount,
            'stories' => $storyCount,
            'test_cases' => $testCaseCount,
            'test_scripts' => $testScriptCount
        ]);

        return [
            'epicCount' => $epicCount,
            'storyCount' => $storyCount,
            'testCaseCount' => $testCaseCount,
            'testScriptCount' => $testScriptCount,
            'batchId' => $importBatchId
        ];
    }




    /**
     * Extract acceptance criteria from Jira issue content.
     */
    private function extractAcceptanceCriteria(string $acceptanceCriteria, string $description): array
    {
        $criteria = [];

        // Try to extract from dedicated field first
        if (!empty($acceptanceCriteria)) {
            // Handle Jira's various AC formats
            if (preg_match_all('/^\s*-\s*(.*?)$/m', $acceptanceCriteria, $matches)) {
                $criteria = array_merge($criteria, $matches[1]);
            } else if (preg_match_all('/^\s*\*\s*(.*?)$/m', $acceptanceCriteria, $matches)) {
                $criteria = array_merge($criteria, $matches[1]);
            } else if (preg_match_all('/^\s*\d+\.\s*(.*?)$/m', $acceptanceCriteria, $matches)) {
                $criteria = array_merge($criteria, $matches[1]);
            } else {
                // If no structured format detected, split by newlines
                $lines = array_filter(array_map('trim', explode("\n", $acceptanceCriteria)));
                $criteria = array_merge($criteria, $lines);
            }
        }

        // Also try to extract from description if needed
        if (empty($criteria) && !empty($description)) {
            // Look for common AC headers in description
            if (preg_match('/acceptance criteria:?\s*(.*?)(?:\n\s*\n|$)/si', $description, $matches)) {
                $acSection = $matches[1];

                // Parse bullet points or numbered items
                if (preg_match_all('/^\s*[-*]\s*(.*?)$/m', $acSection, $matches)) {
                    $criteria = array_merge($criteria, $matches[1]);
                } else if (preg_match_all('/^\s*\d+\.\s*(.*?)$/m', $acSection, $matches)) {
                    $criteria = array_merge($criteria, $matches[1]);
                } else {
                    // Split by newlines as a fallback
                    $lines = array_filter(array_map('trim', explode("\n", $acSection)));
                    $criteria = array_merge($criteria, $lines);
                }
            }
        }

        // Filter and clean criteria
        return array_filter(array_map(function ($item) {
            return trim(strip_tags($item));
        }, $criteria));
    }

    /**
     * Generate test steps from acceptance criteria, adapting for issue type.
     */
    private function generateTestSteps(array $criteria, string $storyTitle, string $issueType = 'Story'): array
    {
        // If we have acceptance criteria, generate more specific steps
        if (!empty($criteria)) {
            $steps = ['Navigate to relevant feature'];

            foreach ($criteria as $criterion) {
                // Convert AC to test step format
                // Remove prefixes like "Given", "When", "Then"
                $step = preg_replace('/^(given|when|then)\s+/i', '', $criterion);

                // Convert to action-oriented language
                $step = preg_replace('/user should be able to/i', 'Verify user can', $step);
                $step = preg_replace('/system should/i', 'Verify system', $step);

                $steps[] = ucfirst($step);
            }

            return $steps;
        }

        // If no criteria, create type-specific default steps
        switch (strtolower($issueType)) {
            case 'bug':
                return [
                    'Navigate to affected feature',
                    'Reproduce the issue described in: ' . $storyTitle,
                    'Verify issue has been fixed'
                ];
            case 'task':
                return [
                    'Navigate to relevant feature',
                    'Verify completion of task: ' . $storyTitle
                ];
            case 'story':
            default:
                return [
                    'Navigate to relevant feature',
                    'Perform actions described in: ' . $storyTitle,
                    'Verify functionality works as expected'
                ];
        }
    }

    /**
     * Generate expected results based on available information and issue type.
     */
    private function generateExpectedResults(array $criteria, string $storyTitle, string $issueType = 'Story'): string
    {
        if (!empty($criteria)) {
            // Convert criteria to verification statements
            $results = array_map(function ($criterion) {
                // Remove prefixes and convert to verification language
                $result = preg_replace('/^(given|when|then)\s+/i', '', $criterion);
                $result = preg_replace('/user should be able to/i', 'User can', $result);
                $result = preg_replace('/system should/i', 'System', $result);

                return "- " . ucfirst($result);
            }, $criteria);

            return "All acceptance criteria are met:\n" . implode("\n", $results);
        }

        // Type-specific fallback results
        switch (strtolower($issueType)) {
            case 'bug':
                return "The issue described in \"$storyTitle\" is resolved and can no longer be reproduced.";
            case 'task':
                return "The task \"$storyTitle\" is completed successfully.";
            case 'story':
            default:
                return "Feature works as described in story \"$storyTitle\".";
        }
    }

    /**
     * Map Jira priority to Arxitest priority.
     */
    private function mapJiraPriorityToArxitest(string $jiraPriority): string
    {
        // Map common Jira priorities to Arxitest priorities
        $mapping = [
            'Highest' => 'high',
            'High' => 'high',
            'Medium' => 'medium',
            'Low' => 'low',
            'Lowest' => 'low',
            'Critical' => 'high',
            'Major' => 'high',
            'Minor' => 'medium',
            'Trivial' => 'low',
            'Blocker' => 'high'
        ];

        return $mapping[trim($jiraPriority)] ?? 'medium';
    }

    /**
     * Check if Jira is connected for a team
     */
    private function isJiraConnectedForTeam(string $teamId): bool
    {
        return ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $teamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->exists();
    }
    /**
     * Find a project with Jira integration in a team
     */
    private function findProjectWithJiraIntegration(string $teamId): Project
    {
        $project = Project::where('team_id', $teamId)
            ->whereHas('projectIntegrations', function ($query) {
                $query->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
                    ->where('is_active', true);
            })
            ->first();

        if (!$project) {
            throw new \Exception("No project found with active Jira integration for this team.");
        }

        return $project;
    }

    /**
     * Import pre-categorized Jira issues
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importCategorizedIssues(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'jira_project_key' => 'required|string',
            'create_new_project' => 'required|in:0,1',
            'arxitest_project_id' => 'required_if:create_new_project,0|uuid|exists:projects,id',
            'new_project_name' => 'required_if:create_new_project,1|string|max:255',
            'issues' => 'required|array',
            'issues.*.id' => 'required|string',
            'issues.*.import_as' => 'required|in:test_suite,story,test_case,skip',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $teamId = session('current_team');
        if (!$teamId) {
            return $this->errorResponse('Team selection required.', 400);
        }

        try {
            // Create new project or use existing one
            if ($request->input('create_new_project') == '1') {
                $name = $request->input('new_project_name');
                $project = Project::create([
                    'name'        => $name,
                    'description' => "Imported from Jira: " . $request->input('jira_project_key'),
                    'team_id'     => $teamId,
                    'settings'    => [
                        'jira_project_key' => $request->input('jira_project_key'),
                        'jira_import' => [
                            'source' => $request->input('jira_project_key'),
                            'date' => now()->toDateTimeString()
                        ]
                    ],
                ]);
            } else {
                $project = Project::findOrFail($request->input('arxitest_project_id'));
                if ($project->team_id !== $teamId) {
                    throw new \Exception('No permission for that project.');
                }
            }

            // Initialize progress tracking
            $this->initializeImportProgress($project->id);

            // Process the categorized issues
            $jiraService = new JiraService($teamId);
            $issueCategories = collect($request->input('issues'))->groupBy('import_as');

            // Queue the import job with the categorization data
            dispatch(new ProcessJiraImportJob(
                $project->id,
                $request->input('jira_project_key'),
                $issueCategories->toArray()
            ));

            return $this->successResponse([
                'project_id' => $project->id,
                'message' => 'Categorized Jira import initiated. Check progress for updates.'
            ]);
        } catch (\Exception $e) {
            Log::error('Categorized Jira import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($project)) {
                $this->setImportCompleted($project->id, false, null, $e->getMessage());
            }

            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
