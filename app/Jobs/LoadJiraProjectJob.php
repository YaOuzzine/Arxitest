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

    // Set reasonable timeouts to prevent long-running jobs
    public $timeout = 300; // 5 minutes max
    public $tries = 2;

    public function __construct(string $teamId, string $projectKey, string $userId)
    {
        $this->teamId = $teamId;
        $this->projectKey = $projectKey;
        $this->userId = $userId;
        // Use a simple format for progress ID
        $this->progressId = "jira_import_{$teamId}_{$projectKey}_" . time();

        // Initialize progress tracking in constructor
        $this->initProgress();
    }

    /**
     * Initialize progress tracking
     */
    protected function initProgress(): void
    {
        Cache::put("progress_{$this->progressId}", [
            'id' => $this->progressId,
            'percent' => 0,
            'message' => 'Job queued, waiting to start...',
            'is_complete' => false,
            'is_success' => null,
            'project_key' => $this->projectKey,
            'team_id' => $this->teamId,
            'user_id' => $this->userId,
            'started_at' => now()->timestamp
        ], now()->addDay());
    }

    public function handle()
    {
        try {
            // Start progress tracking
            $this->updateProgress(10, 'Starting import');

            // Get Jira service
            $jiraService = new JiraService($this->teamId);

            // Fetch project info
            $this->updateProgress(20, 'Fetching project information');
            $projects = $jiraService->getProjects();
            $project = collect($projects)->firstWhere('key', $this->projectKey);

            if (!$project) {
                throw new \Exception("Project not found: {$this->projectKey}");
            }

            // Fetch epics
            $this->updateProgress(40, 'Fetching epics');
            $epics = $jiraService->getIssuesWithJql(
                "project = \"{$this->projectKey}\" AND issuetype = Epic ORDER BY created DESC",
                ['summary', 'description', 'status'],
                100
            );

            // Fetch stories
            $this->updateProgress(60, 'Fetching stories and tasks');
            $stories = $jiraService->getIssuesWithJql(
                "project = \"{$this->projectKey}\" AND issuetype in (Story, Task) ORDER BY created DESC",
                ['summary', 'description', 'status', 'parent', 'labels'],
                200
            );

            // Fetch unassigned issues
            $this->updateProgress(80, 'Fetching unassigned issues');
            $unassigned = $jiraService->getIssuesWithJql(
                "project = \"{$this->projectKey}\" AND issuetype in (Story, Task, Bug) AND \"Epic Link\" is EMPTY ORDER BY created DESC",
                ['summary', 'description', 'status', 'issuetype', 'labels'],
                100
            );

            // Cache results
            $this->updateProgress(90, 'Processing results');
            $result = [
                'project' => $project,
                'epics' => $epics,
                'stories' => $stories,
                'unassigned' => $unassigned,
                'stats' => [
                    'epicCount' => count($epics),
                    'storyCount' => count($stories),
                    'unassignedCount' => count($unassigned)
                ]
            ];

            Cache::put("jira_project_{$this->teamId}_{$this->projectKey}", $result, now()->addDay());

            // Complete progress
            $this->updateProgress(100, 'Import complete', true, true);

            Log::info('Jira project import completed', [
                'project_key' => $this->projectKey,
                'progress_id' => $this->progressId,
                'epics' => count($epics),
                'stories' => count($stories)
            ]);
        } catch (\Exception $e) {
            Log::error('Jira project import failed', [
                'project_key' => $this->projectKey,
                'error' => $e->getMessage()
            ]);

            // Mark as complete with failure
            $this->updateProgress(100, 'Error: ' . $e->getMessage(), true, false);
        }
    }

    /**
     * Update progress
     */
    protected function updateProgress(int $percent, string $message, bool $isComplete = false, bool $isSuccess = null): void
    {
        $data = [
            'id' => $this->progressId,
            'percent' => $percent,
            'message' => $message,
            'is_complete' => $isComplete,
            'project_key' => $this->projectKey,
            'team_id' => $this->teamId,
            'user_id' => $this->userId,
            'last_updated' => now()->timestamp
        ];

        if ($isComplete) {
            $data['is_success'] = $isSuccess;
            $data['completed_at'] = now()->timestamp;
        }

        Cache::put("progress_{$this->progressId}", $data, now()->addDay());
    }

    /**
     * Get progress ID
     */
    public function getProgressId(): string
    {
        return $this->progressId;
    }
}
