<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Story;
use App\Models\TestCase;
use App\Models\SyncMapping;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class JiraSyncService
{
    protected JiraService $jiraService;
    protected Project $project;

    /**
     * Initialize the service with a project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->jiraService = new JiraService($project);
    }

    /**
     * Sync changes from Arxitest to Jira
     *
     * @param array $entities Array of entities to sync
     * @param array $options Sync options
     * @return array Results
     */
    public function pushToJira(array $entities, array $options = []): array
    {
        $result = [
            'success' => 0,
            'failed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'details' => []
        ];

        foreach ($entities as $entity) {
            try {
                // Get mapping if it exists
                $mapping = SyncMapping::where('arxitest_type', get_class($entity))
                    ->where('arxitest_id', $entity->id)
                    ->where('external_system', 'jira')
                    ->first();

                if ($entity instanceof Story) {
                    if ($mapping) {
                        // Update existing issue
                        $issueData = $this->convertStoryToJiraIssue($entity);
                        $response = $this->jiraService->createOrUpdateIssue($issueData, $mapping->external_id);
                        $result['updated']++;
                    } else {
                        // Create new issue
                        $issueData = $this->convertStoryToJiraIssue($entity);
                        $response = $this->jiraService->createOrUpdateIssue($issueData);

                        // Save mapping
                        SyncMapping::create([
                            'arxitest_type' => get_class($entity),
                            'arxitest_id' => $entity->id,
                            'external_system' => 'jira',
                            'external_id' => $response['key'],
                            'last_sync' => now(),
                            'metadata' => ['created_by' => 'push_sync']
                        ]);

                        $result['created']++;
                    }

                    $result['success']++;
                    $result['details'][] = [
                        'entity' => get_class($entity),
                        'id' => $entity->id,
                        'jira_key' => $mapping ? $mapping->external_id : $response['key'],
                        'status' => 'success'
                    ];
                } elseif ($entity instanceof TestCase) {
                    // Process TestCase sync
                    // Similar to Story sync but with different field mapping
                    if ($options['sync_test_cases'] ?? false) {
                        // Implementation for test cases
                    } else {
                        $result['skipped']++;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Jira sync error for {$entity->id}: " . $e->getMessage());
                $result['failed']++;
                $result['details'][] = [
                    'entity' => get_class($entity),
                    'id' => $entity->id,
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }

        return $result;
    }

    /**
     * Pull changes from Jira to Arxitest
     *
     * @param string $jiraProjectKey
     * @param array $options
     * @return array Results
     */
    public function pullFromJira(string $jiraProjectKey, array $options = []): array
    {
        $result = [
            'success' => 0,
            'failed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'details' => []
        ];

        // Get updated issues since last sync
        $lastSync = $options['since'] ?? Carbon::now()->subDays(7);
        $jql = "project = \"{$jiraProjectKey}\" AND updated >= \"{$lastSync->format('Y-m-d')}\"";

        try {
            $issues = $this->jiraService->getIssuesWithJql($jql);

            foreach ($issues as $issue) {
                try {
                    // Check if we already have this issue mapped
                    $mapping = SyncMapping::where('external_system', 'jira')
                        ->where('external_id', $issue['key'])
                        ->first();

                    if ($mapping) {
                        // Update existing entity
                        $entityClass = $mapping->arxitest_type;
                        $entity = $entityClass::find($mapping->arxitest_id);

                        if ($entity) {
                            if ($entity instanceof Story) {
                                $this->updateStoryFromJira($entity, $issue);
                            } elseif ($entity instanceof TestCase) {
                                // Similar for test case
                            }

                            $mapping->update(['last_sync' => now()]);
                            $result['updated']++;
                        } else {
                            // Entity no longer exists
                            $mapping->delete();
                            // Create it as new
                            $this->createEntityFromJira($issue);
                            $result['created']++;
                        }
                    } else {
                        // Create new entity
                        $this->createEntityFromJira($issue);
                        $result['created']++;
                    }

                    $result['success']++;
                } catch (\Exception $e) {
                    Log::error("Error syncing issue {$issue['key']}: " . $e->getMessage());
                    $result['failed']++;
                    $result['details'][] = [
                        'jira_key' => $issue['key'],
                        'error' => $e->getMessage(),
                        'status' => 'error'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Error in Jira pull: " . $e->getMessage());
            $result['details'][] = [
                'error' => "Failed to fetch issues: " . $e->getMessage(),
                'status' => 'error'
            ];
        }

        return $result;
    }

    /**
     * Update a story from Jira issue data - public wrapper for webhook use
     *
     * @param Story $story The story to update
     * @param array $issue The Jira issue data
     * @return void
     */
    public function updateStoryFromJiraWebhook(Story $story, array $issue): void
    {
        $this->updateStoryFromJira($story, $issue);
    }

    /**
     * Create a new entity from Jira issue - public wrapper for webhook use
     *
     * @param array $issue The Jira issue data
     * @return void
     */
    public function createEntityFromJiraWebhook(array $issue): void
    {
        $this->createEntityFromJira($issue);
    }

    /**
     * Convert a Story to Jira issue structure
     */
    protected function convertStoryToJiraIssue(Story $story): array
    {
        return [
            'fields' => [
                'project' => [
                    'key' => $this->getJiraProjectKey()
                ],
                'summary' => $story->title,
                'description' => [
                    'type' => 'doc',
                    'version' => 1,
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $story->description
                                ]
                            ]
                        ]
                    ]
                ],
                'issuetype' => [
                    'name' => 'Story'
                ],
                // Add other fields as needed
                'labels' => ['arxitest-sync']
            ]
        ];
    }

    /**
     * Update a Story from Jira issue data
     */
    protected function updateStoryFromJira(Story $story, array $issue): void
    {
        $story->title = $issue['fields']['summary'];
        $story->description = $this->extractPlainTextFromAdf($issue['fields']['description'] ?? []);
        $story->metadata = array_merge($story->metadata ?? [], [
            'jira_updated' => Carbon::now()->toIso8601String(),
            'jira_status' => $issue['fields']['status']['name'] ?? null
        ]);
        $story->save();
    }

    /**
     * Create a new entity from Jira issue
     */
    protected function createEntityFromJira(array $issue): void
    {
        $issueType = $issue['fields']['issuetype']['name'];

        if (in_array($issueType, ['Story', 'Task', 'Bug'])) {
            // Create Story
            $story = Story::create([
                'project_id' => $this->project->id,
                'title' => $issue['fields']['summary'],
                'description' => $this->extractPlainTextFromAdf($issue['fields']['description'] ?? []),
                'source' => 'jira',
                'external_id' => $issue['key'],
                'metadata' => [
                    'jira_id' => $issue['id'],
                    'jira_key' => $issue['key'],
                    'jira_issue_type' => $issueType,
                    'jira_status' => $issue['fields']['status']['name'],
                    'jira_labels' => $issue['fields']['labels'] ?? [],
                    'jira_import_timestamp' => now()->toIso8601String()
                ]
            ]);

            // Create mapping
            SyncMapping::create([
                'arxitest_type' => Story::class,
                'arxitest_id' => $story->id,
                'external_system' => 'jira',
                'external_id' => $issue['key'],
                'last_sync' => now(),
                'metadata' => ['created_by' => 'pull_sync']
            ]);
        }
    }

    /**
     * Get Jira project key from settings
     */
    protected function getJiraProjectKey(): string
    {
        // Get from project settings
        return $this->project->settings['jira_project_key'] ??
            throw new \Exception("Jira project key not configured");
    }

    /**
     * Extract plain text from Jira's Atlassian Document Format.
     */
    protected function extractPlainTextFromAdf(array $doc): string
    {
        $text = '';
        if (empty($doc)) return $text;

        array_walk_recursive($doc, function ($value, $key) use (&$text) {
            // Whenever we see a "text" key, grab its value
            if ($key === 'text') {
                $text .= $value;
            }
        });
        return $text;
    }
}
