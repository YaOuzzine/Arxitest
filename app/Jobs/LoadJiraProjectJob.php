<?php

namespace App\Jobs;

use App\Models\ProjectImportProgress;
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
            // Initialize progress
            $this->updateProgress(0, 'Starting import');

            // Create service
            $jiraService = new JiraService($this->teamId);

            // Step 1: Get project info (10%)
            $this->updateProgress(10, 'Fetching project information');
            $projects = $jiraService->getProjects();
            $project = collect($projects)->firstWhere('key', $this->projectKey);

            if (!$project) {
                throw new \Exception("Project not found: {$this->projectKey}");
            }

            // Step 2: Get epics (30%)
            $this->updateProgress(30, 'Fetching epics');
            $epics = $jiraService->getIssuesWithJql(
                "project = \"{$this->projectKey}\" AND issuetype = Epic ORDER BY created DESC",
                ['summary', 'description', 'status'],
                100 // Limit to 100 epics
            );

            // Step 3: Get stories and tasks (60%)
            $this->updateProgress(60, 'Fetching stories and tasks');
            $stories = $jiraService->getIssuesWithJql(
                "project = \"{$this->projectKey}\" AND issuetype in (Story, Task) AND \"Epic Link\" is not EMPTY ORDER BY created DESC",
                ['summary', 'description', 'status', 'parent', 'labels'],
                200 // Limit to 200 stories
            );

            // Step 4: Get unassigned issues (80%)
            $this->updateProgress(80, 'Fetching unassigned issues');
            $unassigned = $jiraService->getIssuesWithJql(
                "project = \"{$this->projectKey}\" AND issuetype in (Story, Task, Bug) AND \"Epic Link\" is EMPTY ORDER BY created DESC",
                ['summary', 'description', 'status', 'issuetype', 'labels'],
                100 // Limit to 100 unassigned issues
            );

            // Cache the results (90%)
            $this->updateProgress(90, 'Caching results');
            $result = [
                'project' => $project,
                'epics' => $epics,
                'stories' => $stories,
                'unassigned' => $unassigned
            ];

            Cache::put("jira_project_{$this->teamId}_{$this->projectKey}", $result, now()->addHours(1));

            // Complete
            $this->updateProgress(100, 'Import complete', true);
        } catch (\Exception $e) {
            Log::error('Jira project import failed', [
                'project_key' => $this->projectKey,
                'error' => $e->getMessage()
            ]);

            $this->updateProgress(100, 'Error: ' . $e->getMessage(), true, false);
        }
    }

    protected function updateProgress(int $percent, string $message, bool $isComplete = false, bool $isSuccess = true)
    {
        Cache::put("progress_{$this->progressId}", [
            'percent' => $percent,
            'message' => $message,
            'is_complete' => $isComplete,
            'is_success' => $isSuccess,
            'project_key' => $this->projectKey,
            'team_id' => $this->teamId,
            'user_id' => $this->userId,
            'updated_at' => now()->timestamp
        ], now()->addDay());
    }

    public function getProgressId(): string
    {
        return $this->progressId;
    }
}
