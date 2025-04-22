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

class JiraImportController extends Controller
{
    /**
     * Show options for importing from Jira.
     */
    public function showImportOptions(Request $request)
    {
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Please select a team first.');
        }

        // Check if Jira is connected for the current team
        $jiraConnected = $this->isJiraConnectedForTeam($currentTeamId);

        if (!$jiraConnected) {
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Please connect Jira before attempting to import.');
        }

        $team = Team::findOrFail($currentTeamId);

        try {
            // Use the first project with Jira integration to access Jira API
            $projectWithJira = $this->findProjectWithJiraIntegration($currentTeamId);
            $jiraService = new JiraService($projectWithJira);
            $jiraProjects = $jiraService->getProjects();

            // Get existing projects for the option to import into existing project
            $existingProjects = $team->projects()->get(['id', 'name']);

            return view('dashboard.integrations.jira-import', [
                'jiraProjects' => $jiraProjects,
                'existingProjects' => $existingProjects,
                'teamName' => $team->name
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Jira projects for import options', [
                'error' => $e->getMessage(),
                'team_id' => $currentTeamId
            ]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Could not access Jira projects: ' . $e->getMessage());
        }
    }

    /**
     * Import a Jira project, creating a new Arxitest project or importing into existing one
     */
    public function importProject(Request $request)
    {
        $validated = $request->validate([
            'jira_project_key' => 'required|string|max:100',
            'jira_project_name' => 'required|string|max:255',
            'create_new_project' => 'required|boolean',
            'arxitest_project_id' => 'required_if:create_new_project,false|uuid|exists:projects,id|nullable',
            'new_project_name' => 'required_if:create_new_project,true|string|max:255|nullable',
            'import_epics' => 'sometimes|boolean',
            'import_stories' => 'sometimes|boolean',
            'generate_test_scripts' => 'sometimes|boolean',
            'jql_filter' => 'nullable|string|max:1000',
            'max_issues' => 'nullable|integer|min:0'
        ]);

        $jiraProjectKey = $validated['jira_project_key'];
        $jiraProjectName = $validated['jira_project_name'];
        $importEpics = $request->boolean('import_epics', true);
        $importStories = $request->boolean('import_stories', true);
        $generateTestScripts = $request->boolean('generate_test_scripts', false);
        $jqlFilter = $validated['jql_filter'] ?? '';
        $maxIssues = $validated['max_issues'] ?? 50;
        $createNewProject = $request->boolean('create_new_project');
        $currentTeamId = session('current_team');

        if (!$currentTeamId) {
            Log::error('Team context missing during Jira import');
            return redirect()->route('dashboard.select-team')
                ->with('error', 'Team selection required for Jira import.');
        }

        $team = Team::findOrFail($currentTeamId);

        // Handle AJAX requests for progress updates
        if ($request->wantsJson() && $request->input('check_progress')) {
            $projectId = $createNewProject ? null : $validated['arxitest_project_id'];
            return $this->getImportProgress($request, $projectId);
        }

        DB::beginTransaction();
        try {
            // Step 1: Determine the project to import into
            if ($createNewProject) {
                // Create new Arxitest project
                $newProjectName = $validated['new_project_name'] ?? $jiraProjectName;
                $arxitestProject = Project::create([
                    'name' => $newProjectName,
                    'description' => "Imported from Jira project: $jiraProjectName",
                    'team_id' => $currentTeamId,
                    'settings' => [
                        'jira_import' => [
                            'source_project' => $jiraProjectKey,
                            'import_date' => now()->toDateTimeString()
                        ]
                    ]
                ]);
                Log::info('Created new project for Jira import', [
                    'jira_key' => $jiraProjectKey,
                    'new_project_id' => $arxitestProject->id
                ]);
            } else {
                // Use existing project
                $arxitestProject = Project::findOrFail($validated['arxitest_project_id']);

                // Security check - verify user has access to this project
                if ($arxitestProject->team_id !== $currentTeamId) {
                    throw new \Exception('You do not have permission to import into this project.');
                }
            }

            // Step 2: Get a Jira service client
            $projectWithJira = $this->findProjectWithJiraIntegration($currentTeamId);
            $jiraService = new JiraService($projectWithJira);

            // Step 3: Build JQL query
            $jql = 'project = "' . str_replace('"', '\"', $jiraProjectKey) . '"';

            // Add issue type filter based on selection
            $issueTypes = [];
            if ($importEpics) $issueTypes[] = 'Epic';
            if ($importStories) $issueTypes[] = 'Story';
            // Add Task and Bug as common issue types people want to import
            if ($importStories) {
                $issueTypes[] = 'Task';
                $issueTypes[] = 'Bug';
            }

            if (!empty($issueTypes)) {
                $jql .= ' AND issueType IN ("' . implode('", "', $issueTypes) . '")';
            } else {
                throw new \Exception('No issue types selected for import.');
            }

            // Add custom JQL filter if provided
            if (!empty($jqlFilter)) {
                $jql .= ' AND (' . $jqlFilter . ')';
            }

            $jql .= ' ORDER BY created DESC';

            // Initialize progress tracking
            $this->initializeImportProgress($arxitestProject->id);

            // Step 4: Fetch issues with requested fields
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

            Log::info('Fetching Jira issues for import', [
                'jql' => $jql,
                'project_id' => $arxitestProject->id
            ]);

            $issues = $jiraService->getIssuesWithJql($jql, $fields, $maxIssues);

            // Step 5: Process the import
            $importResult = $this->processJiraImport($issues, $arxitestProject, [
                'importEpics' => $importEpics,
                'importStories' => $importStories,
                'generateTestScripts' => $generateTestScripts
            ]);

            DB::commit();

            // Set success flag in progress tracking
            $this->setImportCompleted($arxitestProject->id, true, $importResult);

            Log::info('Jira project import successful', [
                'jira_key' => $jiraProjectKey,
                'project_id' => $arxitestProject->id,
                'stats' => $importResult
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Import completed successfully!",
                    'data' => $importResult
                ]);
            }

            return redirect()->route('dashboard.projects.show', $arxitestProject->id)
                ->with('success', "Successfully imported {$importResult['epicCount']} epics, " .
                       "{$importResult['storyCount']} stories and created {$importResult['testCaseCount']} test cases.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Jira import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set error flag in progress tracking
            if (isset($arxitestProject)) {
                $this->setImportCompleted($arxitestProject->id, false, null, $e->getMessage());
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
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
                    $epicDescription = Arr::get($issue, 'fields.description', '');

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
                $storyDescription = Arr::get($issue, 'fields.description', '') ?? '';
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
                if ($generateTestScripts) {
                    $testScriptCount++;
                    $this->updateImportProgress($project->id, 'testScripts', 1);
                }
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
     * Get the current progress of an import.
     */
    public function getImportProgress(Request $request, string $projectId = null)
    {
        // If no projectId, try to get it from the request
        if (!$projectId) {
            $projectId = $request->input('project_id');
        }

        // If still no projectId, return empty progress
        if (!$projectId) {
            return response()->json([
                'success' => true,
                'progress' => [
                    'epics' => 0,
                    'stories' => 0,
                    'testCases' => 0,
                    'testScripts' => 0,
                    'completed' => false,
                    'success' => null,
                    'error' => null
                ]
            ]);
        }

        $progress = cache()->get("jira_import_progress_{$projectId}", [
            'epics' => 0,
            'stories' => 0,
            'testCases' => 0,
            'testScripts' => 0,
            'completed' => false,
            'success' => null,
            'error' => null
        ]);

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Initialize import progress tracking
     */
    private function initializeImportProgress(string $projectId)
    {
        $cacheKey = "jira_import_progress_{$projectId}";
        $progress = [
            'epics' => 0,
            'stories' => 0,
            'testCases' => 0,
            'testScripts' => 0,
            'completed' => false,
            'success' => null,
            'error' => null,
            'start_time' => now()->timestamp
        ];

        cache()->put($cacheKey, $progress, now()->addHours(1));
    }

    /**
     * Update the import progress in cache.
     */
    private function updateImportProgress(string $projectId, string $key, int $increment = 1)
    {
        $cacheKey = "jira_import_progress_{$projectId}";
        $progress = cache()->get($cacheKey, [
            'epics' => 0,
            'stories' => 0,
            'testCases' => 0,
            'testScripts' => 0,
            'completed' => false,
            'success' => null,
            'error' => null
        ]);

        if (isset($progress[$key])) {
            $progress[$key] += $increment;
        }

        cache()->put($cacheKey, $progress, now()->addHours(1));
    }

    /**
     * Mark the import as completed.
     */
    private function setImportCompleted(string $projectId, bool $success, $stats = null, string $error = null)
    {
        $cacheKey = "jira_import_progress_{$projectId}";
        $progress = cache()->get($cacheKey, [
            'epics' => 0,
            'stories' => 0,
            'testCases' => 0,
            'testScripts' => 0,
            'completed' => false,
            'success' => null,
            'error' => null
        ]);

        $progress['completed'] = true;
        $progress['success'] = $success;
        $progress['error'] = $error;
        $progress['end_time'] = now()->timestamp;

        if ($stats) {
            $progress['stats'] = $stats;
        }

        cache()->put($cacheKey, $progress, now()->addHours(1));
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
        return array_filter(array_map(function($item) {
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
            $results = array_map(function($criterion) {
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
        return ProjectIntegration::whereHas('project', fn ($q) => $q->where('team_id', $teamId))
            ->whereHas('integration', fn ($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->exists();

    }
    /**
     * Find a project with Jira integration in a team
     */
    private function findProjectWithJiraIntegration(string $teamId): Project
    {
        $project = Project::where('team_id', $teamId)
            ->whereHas('projectIntegrations', function($query) {
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
