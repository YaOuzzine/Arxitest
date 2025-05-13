<?php

namespace App\Jobs;

use App\Models\ExecutionStatus;
use App\Models\Story;
use App\Models\TestCase;
use App\Models\TestSuite;
use App\Services\AI\AIGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessJiraImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId;
    protected $issues;
    protected $options;
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param string $projectId
     * @param array $issues
     * @param array $options
     */
    public function __construct(string $projectId, array $issues, array $options)
    {
        $this->projectId = $projectId;
        $this->issues = $issues;
        $this->options = $options;
        $this->userId = $options['userId'] ?? null;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting Jira import job', [
                'project_id' => $this->projectId,
                'issue_count' => count($this->issues),
                'options' => $this->options
            ]);

            $importEpics = $this->options['importEpics'] ?? true;
            $importStories = $this->options['importStories'] ?? true;
            $generateTestScripts = $this->options['generateTestScripts'] ?? false;

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

            // Track processed items to avoid duplicates
            $processedEpicKeys = [];
            $processedStoryKeys = [];

            // Process Epics -> Test Suites
            if ($importEpics) {
                foreach ($this->issues as $issueIndex => $issue) {
                    if ($this->getIssueType($issue) === 'Epic') {
                        // Skip if we've already processed this epic
                        if (in_array($issue['key'], $processedEpicKeys)) {
                            continue;
                        }
                        $processedEpicKeys[] = $issue['key'];

                        $epicName = $this->getIssueSummary($issue);
                        $epicDescription = $this->getIssueDescription($issue);

                        Log::info('Processing Epic', [
                            'epic_index' => $issueIndex,
                            'epic_key' => $issue['key'],
                            'epic_name' => $epicName
                        ]);

                        // Create or update test suite
                        $testSuite = TestSuite::updateOrCreate(
                            [
                                'project_id' => $this->projectId,
                                'settings->jira_epic_key' => $issue['key']
                            ],
                            [
                                'name' => $epicName,
                                'description' => $epicDescription ?? 'From Epic: ' . $issue['key'],
                                'settings' => [
                                    'jira_epic_id' => $issue['id'],
                                    'jira_epic_key' => $issue['key'],
                                    'import_batch_id' => $importBatchId,
                                    'import_timestamp' => $importTimestamp,
                                    'jira_status' => $this->getIssueStatus($issue),
                                    'jira_labels' => $this->getIssueLabels($issue),
                                    'jira_priority' => $this->getIssuePriority($issue),
                                    'default_priority' => 'medium',
                                    'execution_mode' => 'sequential',
                                ]
                            ]
                        );

                        Log::info('Created/Updated TestSuite from Epic', [
                            'epic_key' => $issue['key'],
                            'test_suite_id' => $testSuite->id,
                            'test_suite_name' => $testSuite->name
                        ]);

                        $epicToSuiteMap[$issue['id']] = $testSuite->id;
                        $epicCount++;

                        // Update progress
                        $this->updateProgress('epics', 1);
                    }
                }
            }

            Log::info('Epic processing completed', [
                'total_epics_processed' => $epicCount,
                'epic_to_suite_map_count' => count($epicToSuiteMap)
            ]);

            // Process Stories/Tasks/Bugs -> Test Cases
            if ($importStories) {
                foreach ($this->issues as $issueIndex => $issue) {
                    $issueType = $this->getIssueType($issue);

                    // Skip epics and unknown issue types
                    if ($issueType === 'Epic' || !in_array($issueType, ['Story', 'Task', 'Bug'])) {
                        continue;
                    }

                    // Skip if we've already processed this story
                    if (in_array($issue['key'], $processedStoryKeys)) {
                        continue;
                    }
                    $processedStoryKeys[] = $issue['key'];

                    $storyTitle = $this->getIssueSummary($issue);
                    $storyDescription = $this->getIssueDescription($issue);
                    $parentEpicId = $this->getParentEpicId($issue);
                    $acceptanceCriteria = $this->getAcceptanceCriteria($issue);

                    Log::info('Processing Story/Task/Bug', [
                        'issue_index' => $issueIndex,
                        'issue_key' => $issue['key'],
                        'issue_type' => $issueType,
                        'issue_title' => $storyTitle,
                        'parent_epic_id' => $parentEpicId
                    ]);

                    // Extract acceptance criteria
                    $criteria = $this->parseAcceptanceCriteria($acceptanceCriteria, $storyDescription);

                    // Determine suite to associate with
                    $suiteId = null;
                    if ($parentEpicId && isset($epicToSuiteMap[$parentEpicId])) {
                        $suiteId = $epicToSuiteMap[$parentEpicId];
                        Log::info('Found parent epic suite', [
                            'issue_key' => $issue['key'],
                            'parent_epic_id' => $parentEpicId,
                            'suite_id' => $suiteId
                        ]);
                    } else {
                        // Create default suite if needed
                        if (!$defaultSuite) {
                            $defaultSuite = TestSuite::firstOrCreate(
                                [
                                    'project_id' => $this->projectId,
                                    'name' => 'Imported Issues (Uncategorized)'
                                ],
                                [
                                    'description' => 'Issues without epics imported from Jira.',
                                    'settings' => [
                                        'import_batch_id' => $importBatchId,
                                        'import_timestamp' => $importTimestamp,
                                        'default_priority' => 'medium',
                                        'execution_mode' => 'sequential',
                                    ]
                                ]
                            );
                            Log::info('Created default test suite for uncategorized issues', [
                                'default_suite_id' => $defaultSuite->id
                            ]);
                        }
                        $suiteId = $defaultSuite->id;
                        Log::info('Using default suite', [
                            'issue_key' => $issue['key'],
                            'default_suite_id' => $suiteId
                        ]);
                    }

                    // Create the Arxitest Story
                    $arxitestStory = Story::updateOrCreate(
                        [
                            'external_id' => $issue['key'],
                            'source' => 'jira'
                        ],
                        [
                            'project_id' => $this->projectId,
                            'title' => $storyTitle,
                            'description' => $storyDescription,
                            'metadata' => [
                                'jira_id' => $issue['id'],
                                'jira_key' => $issue['key'],
                                'jira_issue_type' => $issueType,
                                'jira_status' => $this->getIssueStatus($issue),
                                'jira_priority' => $this->getIssuePriority($issue),
                                'jira_labels' => $this->getIssueLabels($issue),
                                'jira_assignee' => $this->getIssueAssignee($issue),
                                'import_batch_id' => $importBatchId,
                                'import_timestamp' => $importTimestamp
                            ]
                        ]
                    );

                    Log::info('Created/Updated Story', [
                        'issue_key' => $issue['key'],
                        'story_id' => $arxitestStory->id,
                        'story_title' => $arxitestStory->title
                    ]);

                    $storyCount++;
                    $this->updateProgress('stories', 1);

                    // Generate test steps
                    $testSteps = $this->generateTestSteps($criteria, $storyTitle, $issueType);

                    // Create test case
                    $testCase = TestCase::firstOrCreate(
                        [
                            'suite_id' => $suiteId,
                            'title' => "Verify: " . Str::limit($storyTitle, 90)
                        ],
                        [
                            'story_id' => $arxitestStory->id,
                            'steps' => $testSteps,
                            'expected_results' => $this->generateExpectedResults($criteria, $storyTitle, $issueType),
                            'priority' => $this->mapJiraPriorityToArxitest(
                                $this->getIssuePriority($issue)
                            ),
                            'status' => 'draft',
                            'tags' => array_merge(
                                ['jira-import', $issue['key'], strtolower($issueType)],
                                $this->getIssueLabels($issue)
                            )
                        ]
                    );

                    Log::info('Created/Updated TestCase', [
                        'issue_key' => $issue['key'],
                        'test_case_id' => $testCase->id,
                        'test_case_title' => $testCase->title,
                        'suite_id' => $suiteId,
                        'step_count' => count($testSteps)
                    ]);

                    $testCaseCount++;
                    $this->updateProgress('testCases', 1);

                    // Generate test scripts if requested
                    if ($generateTestScripts) {
                        try {
                            $this->generateTestScript($testCase, $importBatchId);
                            $testScriptCount++;
                            $this->updateProgress('testScripts', 1);
                        } catch (\Exception $e) {
                            Log::error('Failed to generate test script', [
                                'test_case_id' => $testCase->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }

            // Log detailed completion summary
            Log::info('Jira import completed successfully', [
                'project_id' => $this->projectId,
                'issues_processed' => count($this->issues),
                'epics_created' => $epicCount,
                'stories_created' => $storyCount,
                'test_cases_created' => $testCaseCount,
                'test_scripts_created' => $testScriptCount,
                'import_batch_id' => $importBatchId
            ]);

            // Update final progress and mark as completed
            $this->setImportCompleted(true, [
                'epicCount' => $epicCount,
                'storyCount' => $storyCount,
                'testCaseCount' => $testCaseCount,
                'testScriptCount' => $testScriptCount,
                'batchId' => $importBatchId
            ]);

        } catch (\Exception $e) {
            Log::error('Jira import job failed', [
                'project_id' => $this->projectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark import as failed
            $this->setImportCompleted(false, null, $e->getMessage());
        }
    }

    /**
     * Update progress in cache
     */
    protected function updateProgress(string $key, int $count = 1): void
    {
        $cacheKey = "jira_import_progress_{$this->projectId}";
        $progress = cache()->get($cacheKey, []);
        $progress[$key] = ($progress[$key] ?? 0) + $count;
        cache()->put($cacheKey, $progress, 3600);

        Log::info("Updated progress counter", [
            'key' => $key,
            'increment' => $count,
            'new_total' => $progress[$key]
        ]);
    }

    /**
     * Mark import as completed
     */
    protected function setImportCompleted(bool $success, ?array $stats = null, ?string $error = null): void
    {
        $cacheKey = "jira_import_progress_{$this->projectId}";
        $progress = cache()->get($cacheKey, []);
        $progress['completed'] = true;
        $progress['success'] = $success;
        if ($stats !== null) $progress['stats'] = $stats;
        if ($error !== null) $progress['error'] = $error;
        $progress['end_time'] = now()->timestamp;
        cache()->put($cacheKey, $progress, 3600);

        Log::info("Import marked as completed", [
            'success' => $success,
            'stats' => $stats,
            'error' => $error
        ]);
    }

    // Helper methods for extracting data from Jira issues

    protected function getIssueType(array $issue): string
    {
        return $issue['fields']['issuetype']['name'] ?? 'Unknown';
    }

    protected function getIssueSummary(array $issue): string
    {
        return $issue['fields']['summary'] ?? 'Untitled ' . $issue['key'];
    }

    protected function getIssueDescription(array $issue): string
    {
        $rawDesc = $issue['fields']['description'] ?? '';
        if (is_array($rawDesc)) {
            // Handle Jira's Atlassian Document Format (ADF)
            return $this->extractTextFromADF($rawDesc);
        }
        return $rawDesc;
    }

    protected function getIssueStatus(array $issue): string
    {
        return $issue['fields']['status']['name'] ?? 'Unknown';
    }

    protected function getIssueLabels(array $issue): array
    {
        return $issue['fields']['labels'] ?? [];
    }

    protected function getIssuePriority(array $issue): string
    {
        return $issue['fields']['priority']['name'] ?? 'Medium';
    }

    protected function getIssueAssignee(array $issue): ?string
    {
        return $issue['fields']['assignee']['displayName'] ?? null;
    }

    protected function getParentEpicId(array $issue): ?string
    {
        return $issue['fields']['parent']['id'] ?? null;
    }

    protected function getAcceptanceCriteria(array $issue): string
    {
        // Common field for acceptance criteria
        return $issue['fields']['customfield_10005'] ?? '';
    }

    /**
     * Extract plain text from Atlassian Document Format
     */
    protected function extractTextFromADF(array $doc): string
    {
        $text = '';

        // Process content nodes recursively
        if (isset($doc['content']) && is_array($doc['content'])) {
            foreach ($doc['content'] as $node) {
                if (isset($node['text'])) {
                    $text .= $node['text'] . ' ';
                } elseif (isset($node['content']) && is_array($node['content'])) {
                    $text .= $this->extractTextFromADF($node) . ' ';
                }
            }
        }

        return trim($text);
    }

    /**
     * Parse acceptance criteria from text
     */
    protected function parseAcceptanceCriteria(string $acceptanceCriteria, string $description): array
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
     * Generate test steps from acceptance criteria
     */
    protected function generateTestSteps(array $criteria, string $storyTitle, string $issueType): array
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
     * Generate expected results based on criteria
     */
    protected function generateExpectedResults(array $criteria, string $storyTitle, string $issueType): string
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
     * Map Jira priority to Arxitest priority
     */
    protected function mapJiraPriorityToArxitest(string $jiraPriority): string
    {
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
     * Generate test script for a test case using AI
     */
    protected function generateTestScript(TestCase $testCase, string $importBatchId): void
    {
        try {
            $framework = 'selenium-python'; // Default framework

            // Use the AI service to generate a script
            $aiService = app(AIGenerationService::class);
            $scriptContent = $aiService->generateTestScript(
                "Generate a test script for: " . $testCase->title,
                [
                    'test_case_id' => $testCase->id,
                    'framework_type' => $framework
                ]
            );

            // Create test script record
            $testScript = new \App\Models\TestScript();
            $testScript->test_case_id = $testCase->id;
            $testScript->creator_id = $this->userId; // System user ID or admin
            $testScript->name = "{$testCase->title} - {$framework} Script";
            $testScript->framework_type = $framework;
            $testScript->script_content = $scriptContent['content'] ?? '';
            $testScript->metadata = [
                'created_through' => 'ai',
                'source' => 'jira-import',
                'import_batch_id' => $importBatchId
            ];
            $testScript->save();

            Log::info('Generated test script for test case', [
                'test_case_id' => $testCase->id,
                'test_script_id' => $testScript->id
            ]);
        } catch (\Exception $e) {
            // Log error but continue with other test cases
            Log::error('Failed to generate script for test case', [
                'test_case_id' => $testCase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
