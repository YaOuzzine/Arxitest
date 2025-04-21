<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\Project;
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

class JiraImportController extends Controller
{
    /**
     * Show options for importing from Jira.
     */
    public function showImportOptions(Request $request)
    {
        $arxitestProjectId = $request->query('project_id');
        if (!$arxitestProjectId) {
            return redirect()->route('dashboard.projects')
                ->with('error', 'Please select a project before importing.');
        }

        $arxitestProject = Project::find($arxitestProjectId);
        if (!$arxitestProject || $arxitestProject->team_id !== session('current_team')) {
            Log::warning('Invalid project access for Jira import', [
                'user_id' => Auth::id(),
                'project_id' => $arxitestProjectId,
                'team_id' => session('current_team')
            ]);
            return redirect()->route('dashboard.projects')
                ->with('error', 'You do not have access to this project.');
        }

        try {
            $jiraService = new JiraService($arxitestProject);
            $jiraProjects = $jiraService->getProjects();

            return view('dashboard.integrations.jira-import', [
                'jiraProjects' => $jiraProjects,
                'arxitestProjectId' => $arxitestProject->id,
                'arxitestProjectName' => $arxitestProject->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Jira projects', [
                'error' => $e->getMessage(),
                'project_id' => $arxitestProjectId
            ]);
            return redirect()->route('dashboard.integrations.index', ['project_id' => $arxitestProjectId])
                ->with('error', 'Could not access Jira projects: ' . $e->getMessage());
        }
    }

    /**
     * Import a Jira project into Arxitest with enhanced options and mapping.
     */
    public function importProject(Request $request)
    {
        $validated = $request->validate([
            'jira_project_key' => 'required|string|max:100',
            'jira_project_name' => 'required|string|max:255',
            'arxitest_project_id' => 'required|uuid|exists:projects,id',
            'import_epics' => 'sometimes|boolean',
            'import_stories' => 'sometimes|boolean',
            'generate_test_scripts' => 'sometimes|boolean',
            'jql_filter' => 'nullable|string|max:1000',
            'max_issues' => 'nullable|integer|min:0'
        ]);

        $jiraProjectKey = $validated['jira_project_key'];
        $jiraProjectName = $validated['jira_project_name'];
        $arxitestProjectId = $validated['arxitest_project_id'];
        $importEpics = $request->boolean('import_epics', true);
        $importStories = $request->boolean('import_stories', true);
        $generateTestScripts = $request->boolean('generate_test_scripts', false);
        $jqlFilter = $validated['jql_filter'] ?? '';
        $maxIssues = $validated['max_issues'] ?? 50;

        $arxitestProject = Project::findOrFail($arxitestProjectId);
        if ($arxitestProject->team_id !== session('current_team')) {
            Log::warning('Unauthorized Jira import attempt', [
                'user_id' => Auth::id(),
                'project_id' => $arxitestProjectId
            ]);
            return back()->with('error', 'You do not have permission to import into this project.');
        }

        Log::info('Starting Jira project import with extended options', [
            'jira_key' => $jiraProjectKey,
            'project_id' => $arxitestProjectId,
            'import_epics' => $importEpics,
            'import_stories' => $importStories,
            'generate_scripts' => $generateTestScripts,
            'has_jql_filter' => !empty($jqlFilter),
            'max_issues' => $maxIssues,
        ]);

        // Handle AJAX requests for progress updates
        if ($request->wantsJson() && $request->input('check_progress')) {
            return $this->getImportProgress($request, $arxitestProjectId);
        }

        DB::beginTransaction();
        try {
            $jiraService = new JiraService($arxitestProject);

            // Build JQL query
            $jql = 'project = "' . $jiraProjectKey . '"';

            // Add issue type filter based on selection
            $issueTypes = [];
            if ($importEpics) $issueTypes[] = 'Epic';
            if ($importStories) $issueTypes[] = 'Story';

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

            // Fetch issues with requested fields
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
                'customfield_10005' // Add commonly used field for acceptance criteria
            ];

            $issues = $jiraService->getIssuesWithJql($jql, $fields, $maxIssues);

            // Track import progress
            $epicToSuiteMap = [];
            $storiesCreated = 0;
            $testCasesCreated = 0;
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

                        // Enhanced metadata with more fields
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
                                'project_id' => $arxitestProject->id,
                                'settings->jira_epic_key' => $issue['key']
                            ],
                            [
                                'name' => $epicName,
                                'description' => $epicDescription ?? 'From Epic: ' . $issue['key'],
                                'settings' => $suiteSettings
                            ]
                        );

                        $epicToSuiteMap[$issue['id']] = $testSuite->id;

                        // Track progress for reporting
                        $this->updateImportProgress($arxitestProjectId, 'epics', 1);
                    }
                }
            }

            // Process Stories -> Stories & Test Cases (only if importing stories)
            if ($importStories) {
                foreach ($issues as $issue) {
                    if (Arr::get($issue, 'fields.issuetype.name') === 'Story') {
                        $storyTitle = Arr::get($issue, 'fields.summary', 'Untitled Story ' . $issue['key']);
                        $storyDescription = Arr::get($issue, 'fields.description', '') ?? '';
                        $parentEpicId = Arr::get($issue, 'fields.parent.id');
                        $acceptanceCriteria = Arr::get($issue, 'fields.customfield_10005', ''); // Common field for AC

                        // Extract acceptance criteria to generate better test cases
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
                                        'project_id' => $arxitestProject->id,
                                        'name' => 'Imported (Uncategorized)'
                                    ],
                                    [
                                        'description' => 'Imported Jira stories without epics.',
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

                        // Create the story
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
                        $storiesCreated++;
                        $this->updateImportProgress($arxitestProjectId, 'stories', 1);

                        // Generate better test steps from acceptance criteria
                        $testSteps = $this->generateTestSteps($criteria, $storyTitle);

                        // Create a test case for the story
                        $testCase = ArxitestTestCase::firstOrCreate(
                            [
                                'story_id' => $arxitestStory->id,
                                'suite_id' => $suiteId,
                                'title' => 'Verify: ' . Str::limit($storyTitle, 100)
                            ],
                            [
                                'steps' => $testSteps,
                                'expected_results' => $this->generateExpectedResults($criteria, $storyTitle),
                                'priority' => $this->mapJiraPriorityToArxitest(
                                    Arr::get($issue, 'fields.priority.name', 'Medium')
                                ),
                                'status' => 'draft',
                                'tags' => array_merge(
                                    ['jira-import', $issue['key']],
                                    Arr::get($issue, 'fields.labels', [])
                                )
                            ]
                        );
                        $testCasesCreated++;
                        $this->updateImportProgress($arxitestProjectId, 'testCases', 1);

                        // Generate test scripts if requested
                        if ($generateTestScripts && $testCase) {
                            // We would call the AI generation here or queue a job
                            // For now, just update the progress tracking
                            $this->updateImportProgress($arxitestProjectId, 'testScripts', 1);
                        }
                    }
                }
            }

            DB::commit();
            Log::info('Jira project import successful', [
                'jira_key' => $jiraProjectKey,
                'stories' => $storiesCreated,
                'test_cases' => $testCasesCreated,
                'batch_id' => $importBatchId
            ]);

            // Set success flag in progress tracking
            $this->setImportCompleted($arxitestProjectId, true);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Import completed successfully!",
                    'data' => [
                        'epics_imported' => $importEpics ? count($epicToSuiteMap) : 0,
                        'stories_imported' => $storiesCreated,
                        'test_cases_created' => $testCasesCreated
                    ]
                ]);
            }

            return redirect()->route('dashboard.projects.show', $arxitestProject->id)
                ->with('success', "Imported " . ($importEpics ? count($epicToSuiteMap) . " epics, " : "") .
                        "{$storiesCreated} stories and created {$testCasesCreated} test cases from '{$jiraProjectName}'.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Jira import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set error flag in progress tracking
            $this->setImportCompleted($arxitestProjectId, false, $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('integrations.jira.import.options', ['project_id' => $arxitestProjectId])
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the current progress of an import.
     */
    private function getImportProgress(Request $request, string $projectId)
    {
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
    private function setImportCompleted(string $projectId, bool $success, string $error = null)
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
     * Generate test steps from acceptance criteria.
     */
    private function generateTestSteps(array $criteria, string $storyTitle): array
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

        // Fallback to generic steps
        return [
            'Navigate to relevant feature',
            'Perform actions described in: ' . $storyTitle,
            'Verify functionality works as expected'
        ];
    }

    /**
     * Generate expected results based on available information.
     */
    private function generateExpectedResults(array $criteria, string $storyTitle): string
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

        // Fallback
        return "Feature works as described in story \"$storyTitle\".";
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
}
