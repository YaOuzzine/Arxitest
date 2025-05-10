<?php

namespace App\Jobs;

use App\Services\JiraService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoadJiraProjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $teamId;
    protected $projectKey;
    protected $userId;
    protected $progressId;

    public function __construct(string $teamId, string $projectKey, string $userId)
    {
        $this->teamId = $teamId;
        $this->projectKey = $projectKey;
        $this->userId = $userId;
        $this->progressId = "jira_import_{$teamId}_{$projectKey}_" . time();
    }

    public function handle()
    {
        try {
            Log::info('Starting Jira project load', [
                'project_key' => $this->projectKey,
                'team_id' => $this->teamId,
                'job_id' => $this->progressId
            ]);

            // Initialize progress
            $this->updateProgress(0, 'Starting Jira project data import');

            // Create service
            $jiraService = new JiraService($this->teamId);

            // Step 1: Get project details (10%)
            $this->updateProgress(5, 'Fetching project information');
            $projects = $jiraService->getProjects();
            $project = collect($projects)->firstWhere('key', $this->projectKey);

            if (!$project) {
                throw new \Exception("Project not found: {$this->projectKey}");
            }

            $this->updateProgress(10, 'Project information retrieved successfully');
            Log::info('Project info retrieved', ['project' => $project['name']]);

            // Step 2: Get epics (30%)
            $this->updateProgress(15, 'Fetching epics');

            $epicJql = "project = \"{$this->projectKey}\" AND issuetype = Epic ORDER BY created DESC";
            $epicFields = ['summary', 'description', 'status', 'priority', 'labels', 'created', 'updated'];

            Log::debug('Executing epic JQL query', ['jql' => $epicJql]);
            $epics = $jiraService->getIssuesWithJql(
                $epicJql,
                $epicFields,
                0 // No limit - get all epics
            );

            $epicCount = count($epics);
            $this->updateProgress(30, "Retrieved {$epicCount} epics");
            Log::info('Epics retrieved', ['count' => $epicCount]);

            // Step 3: Get stories (60%)
            $this->updateProgress(35, 'Fetching stories and tasks');

            $storyFields = [
                'summary',
                'description',
                'status',
                'issuetype',
                'parent',
                'labels',
                'priority',
                'created',
                'updated'
            ];

            // Try different epic link field variants (depends on JIRA configuration)
            $storiesJql = "project = \"{$this->projectKey}\" AND issuetype in (Story, Task) AND (" .
                "\"Epic Link\" is not EMPTY OR parent is not EMPTY OR parent in issueFunction(issueType = Epic))";

            Log::debug('Executing stories JQL query', ['jql' => $storiesJql]);
            $stories = $jiraService->getIssuesWithJql(
                $storiesJql,
                $storyFields,
                0 // No limit
            );

            $storyCount = count($stories);
            $this->updateProgress(60, "Retrieved {$storyCount} stories and tasks");
            Log::info('Stories retrieved', ['count' => $storyCount]);

            // Step 4: Get unassigned issues (80%)
            $this->updateProgress(65, 'Fetching unassigned issues');

            // Handle different ways epics might be linked
            $unassignedJql = "project = \"{$this->projectKey}\" AND issuetype in (Story, Task, Bug) AND " .
                "\"Epic Link\" is EMPTY AND parent is EMPTY";

            Log::debug('Executing unassigned issues JQL query', ['jql' => $unassignedJql]);
            $unassigned = $jiraService->getIssuesWithJql(
                $unassignedJql,
                $storyFields,
                0 // No limit
            );

            $unassignedCount = count($unassigned);
            $this->updateProgress(80, "Retrieved {$unassignedCount} unassigned issues");
            Log::info('Unassigned issues retrieved', ['count' => $unassignedCount]);

            // Cache the results (90%)
            $this->updateProgress(90, 'Organizing and caching results');

            $totalIssues = $epicCount + $storyCount + $unassignedCount;

            $result = [
                'project' => $project,
                'epics' => $epics,
                'stories' => $stories,
                'unassigned' => $unassigned,
                'metadata' => [
                    'total_issues' => $totalIssues,
                    'epic_count' => $epicCount,
                    'story_count' => $storyCount,
                    'unassigned_count' => $unassignedCount,
                    'fetched_at' => now()->toIso8601String(),
                ]
            ];

            Cache::put("jira_project_{$this->teamId}_{$this->projectKey}", $result, now()->addMinutes(5));

            Log::info('Data successfully cached', [
                'project_key' => $this->projectKey,
                'total_issues' => $totalIssues
            ]);

            // Complete
            $this->updateProgress(100, 'Import complete', true, true, [
                'total_issues' => $totalIssues,
                'epics' => $epicCount,
                'stories' => $storyCount,
                'unassigned' => $unassignedCount
            ]);

            Log::info('Jira project import completed', [
                'project_key' => $this->projectKey,
                'status' => 'success',
                'total_issues' => $totalIssues
            ]);
        } catch (\Exception $e) {
            Log::error('Jira project import failed', [
                'project_key' => $this->projectKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->updateProgress(100, 'Error: ' . $e->getMessage(), true, false);
        }
    }

    protected function updateProgress(int $percent, string $message, bool $isComplete = false, bool $isSuccess = true, array $stats = [])
    {
        $progressData = [
            'percent' => $percent,
            'message' => $message,
            'is_complete' => $isComplete,
            'is_success' => $isSuccess,
            'project_key' => $this->projectKey,
            'team_id' => $this->teamId,
            'user_id' => $this->userId,
            'updated_at' => now()->timestamp
        ];

        if (!empty($stats)) {
            $progressData['stats'] = $stats;
        }

        Cache::put("progress_{$this->progressId}", $progressData, now()->addDay());

        Log::debug('Progress updated', [
            'job_id' => $this->progressId,
            'percent' => $percent,
            'message' => $message
        ]);
    }

    public function getProgressId(): string
    {
        return $this->progressId;
    }
}
