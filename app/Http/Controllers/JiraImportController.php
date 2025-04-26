<?php

namespace App\Http\Controllers;

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

class JiraImportController extends Controller
{
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
     */
    public function importProject(Request $request)
    {
        $validated = $request->validate([
            'jira_project_key'       => 'required|string|max:100',
            'jira_project_name'      => 'required|string|max:255',
            'create_new_project'     => 'required|boolean',
            'arxitest_project_id'    => 'required_if:create_new_project,false|uuid|exists:projects,id',
            'new_project_name'       => 'required_if:create_new_project,true|string|max:255',
            'import_epics'           => 'sometimes|boolean',
            'import_stories'         => 'sometimes|boolean',
            'generate_test_scripts'  => 'sometimes|boolean',
            'jql_filter'             => 'nullable|string|max:1000',
            'max_issues'             => 'nullable|integer|min:0',
        ]);

        $teamId = session('current_team');
        if (! $teamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Team selection required.');
        }

        if ($request->wantsJson() && $request->input('check_progress')) {
            $projectId = $request->boolean('create_new_project') ? null : $validated['arxitest_project_id'];
            return $this->getImportProgress($request, $projectId);
        }

        DB::beginTransaction();
        try {
            if ($request->boolean('create_new_project')) {
                $name    = $validated['new_project_name'];
                $project = Project::create([
                    'name'        => $name,
                    'description' => "Imported from Jira: " . $validated['jira_project_name'],
                    'team_id'     => $teamId,
                    'settings'    => ['jira_import' => ['source' => $validated['jira_project_key'], 'date' => now()->toDateTimeString()]],
                ]);
                Log::info('Created new project for Jira import', ['project_id' => $project->id, 'jira_key' => $validated['jira_project_key']]);
            } else {
                $project = Project::findOrFail($validated['arxitest_project_id']);
                if ($project->team_id !== $teamId) {
                    throw new \Exception('No permission for that project.');
                }
            }

            $jiraService = new JiraService($teamId);

            // Build JQL
            $jqlParts = ['project = "' . str_replace('"', '\"', $validated['jira_project_key']) . '"'];
            $types = [];
            if ($request->boolean('import_epics', true))   $types[] = 'Epic';
            if ($request->boolean('import_stories', true))  $types = array_merge($types, ['Story', 'Task', 'Bug']);
            if ($types) {
                $jqlParts[] = 'issueType IN ("' . implode('","', $types) . '")';
            }
            if ($validated['jql_filter']) {
                $jqlParts[] = '(' . $validated['jql_filter'] . ')';
            }
            $jql = implode(' AND ', $jqlParts) . ' ORDER BY created DESC';

            $this->initializeImportProgress($project->id);

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
                'customfield_10005'
            ];

            $issues = $jiraService->getIssuesWithJql($jql, $fields, $validated['max_issues'] ?? 50);

            $result = $this->processJiraImport(
                $issues,
                $project,
                [
                    'importEpics'         => $request->boolean('import_epics', true),
                    'importStories'       => $request->boolean('import_stories', true),
                    'generateTestScripts' => $request->boolean('generate_test_scripts', false),
                ]
            );

            DB::commit();

            $this->setImportCompleted(
                $project->id,
                true,
                $result,
                null,
                ['attempted' => $validated['generate_test_scripts'] ?? false, 'count' => $result['testScriptCount'] ?? 0]
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $result]);
            }

            return redirect()->route('dashboard.projects.show', $project->id)
                ->with('success', 'Imported ' . $result['epicCount'] . ' epics, ' . $result['storyCount'] . ' stories.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Jira import failed', ['error' => $e->getMessage()]);

            if (isset($project)) {
                $this->setImportCompleted($project->id, false, null, $e->getMessage());
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function getImportProgress(Request $request, ?string $projectId = null)
    {
        $projectId = $projectId ?: $request->input('project_id');
        if (! $projectId) {
            return response()->json(['success' => true, 'progress' => [
                'epics' => 0,
                'stories' => 0,
                'testCases' => 0,
                'testScripts' => 0,
                'completed' => false,
                'success' => null,
                'error' => null
            ]]);
        }

        $progress = cache()->get("jira_import_progress_{$projectId}", []);
        return response()->json(['success' => true, 'progress' => $progress]);
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

    protected function updateImportProgress(string $projectId, string $key, int $inc = 1): void
    {
        $keyFull = "jira_import_progress_{$projectId}";
        $prog = cache()->get($keyFull, []);
        $prog[$key] = ($prog[$key] ?? 0) + $inc;
        cache()->put($keyFull, $prog, 3600);
    }

    protected function setImportCompleted(string $projectId, bool $success, $stats = null, ?string $error = null, ?array $scriptStatus = null): void
    {
        $keyFull = "jira_import_progress_{$projectId}";
        $prog = cache()->get($keyFull, []);
        $prog['completed'] = true;
        $prog['success']   = $success;
        if ($error     !== null) $prog['error']   = $error;
        if ($stats     !== null) $prog['stats']   = $stats;
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
}
