<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Integration;
use App\Models\Story;
use App\Models\TestCase;
use App\Services\JiraSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JiraSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId;
    protected $direction;
    protected $entityTypes;
    protected $options;
    protected $progressId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $projectId, string $direction, array $entityTypes, array $options = [])
    {
        $this->projectId = $projectId;
        $this->direction = $direction;
        $this->entityTypes = $entityTypes;
        $this->options = $options;
        $this->progressId = Str::uuid()->toString();

        // Initialize progress tracker
        $this->initializeProgress();
    }

    /**
     * Get the progress ID
     */
    public function getProgressId(): string
    {
        return $this->progressId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $project = Project::findOrFail($this->projectId);
            $jiraProjectKey = $project->settings['jira_project_key'] ?? null;

            if (!$jiraProjectKey) {
                throw new \Exception("Jira project key not configured for project {$this->projectId}");
            }

            // Update progress to 10%
            $this->updateProgress(10, 'Initializing synchronization');

            // Create sync service
            $syncService = new JiraSyncService($project);

            // Update progress to 20%
            $this->updateProgress(20, 'Preparing entities');

            // Process based on direction
            if ($this->direction === 'pull' || $this->direction === 'both') {
                $this->updateProgress(30, 'Pulling changes from Jira');

                $pullResults = $syncService->pullFromJira($jiraProjectKey, $this->options);

                $this->updateProgress(50, "Pulled {$pullResults['success']} items from Jira");

                // Save results in progress
                Cache::put("jira_sync_progress_{$this->progressId}", array_merge(
                    Cache::get("jira_sync_progress_{$this->progressId}", []),
                    ['pull_results' => $pullResults]
                ), now()->addHours(24));
            }

            if ($this->direction === 'push' || $this->direction === 'both') {
                $this->updateProgress(60, 'Preparing to push changes to Jira');

                // Collect entities to push
                $entitiesToPush = [];

                if (in_array('story', $this->entityTypes)) {
                    // Get stories
                    $stories = Story::where('project_id', $this->projectId)
                        ->when(!empty($this->options['since']), function($query) {
                            $query->where('updated_at', '>=', $this->options['since']);
                        })
                        ->get();

                    $entitiesToPush = array_merge($entitiesToPush, $stories->all());
                }

                if (in_array('test_case', $this->entityTypes)) {
                    // Get test cases
                    $testCases = TestCase::whereHas('testSuite', function($query) {
                        $query->where('project_id', $this->projectId);
                    })
                    ->when(!empty($this->options['since']), function($query) {
                        $query->where('updated_at', '>=', $this->options['since']);
                    })
                    ->get();

                    $entitiesToPush = array_merge($entitiesToPush, $testCases->all());
                }

                $this->updateProgress(70, "Pushing " . count($entitiesToPush) . " items to Jira");

                // Push to Jira
                $pushResults = $syncService->pushToJira($entitiesToPush, $this->options);

                $this->updateProgress(90, "Pushed {$pushResults['success']} items to Jira");

                // Save results in progress
                Cache::put("jira_sync_progress_{$this->progressId}", array_merge(
                    Cache::get("jira_sync_progress_{$this->progressId}", []),
                    ['push_results' => $pushResults]
                ), now()->addHours(24));
            }

            // Add sync history to project integration
            $this->addSyncHistory($project, [
                'direction' => $this->direction,
                'entity_types' => $this->entityTypes,
                'pull_results' => $pullResults ?? null,
                'push_results' => $pushResults ?? null,
                'completed_at' => now()->toIso8601String()
            ]);

            // Set progress to complete
            $this->setProgressCompleted(true);

        } catch (\Exception $e) {
            Log::error("Jira sync job failed: " . $e->getMessage());

            // Update progress with error
            $this->setProgressCompleted(false, $e->getMessage());
        }
    }

    /**
     * Initialize the progress tracking
     */
    protected function initializeProgress(): void
    {
        Cache::put("jira_sync_progress_{$this->progressId}", [
            'id' => $this->progressId,
            'project_id' => $this->projectId,
            'direction' => $this->direction,
            'entity_types' => $this->entityTypes,
            'is_complete' => false,
            'is_success' => null,
            'percent' => 0,
            'message' => 'Initializing sync',
            'started_at' => now()->timestamp,
            'updated_at' => now()->timestamp
        ], now()->addHours(24));
    }

    /**
     * Update the progress
     */
    protected function updateProgress(int $percent, string $message): void
    {
        $progress = Cache::get("jira_sync_progress_{$this->progressId}", []);

        $progress['percent'] = $percent;
        $progress['message'] = $message;
        $progress['updated_at'] = now()->timestamp;

        Cache::put("jira_sync_progress_{$this->progressId}", $progress, now()->addHours(24));

        Log::info("Jira sync progress: {$percent}% - {$message}");
    }

    /**
     * Set progress to completed
     */
    protected function setProgressCompleted(bool $success, ?string $errorMessage = null): void
    {
        $progress = Cache::get("jira_sync_progress_{$this->progressId}", []);

        $progress['is_complete'] = true;
        $progress['is_success'] = $success;
        $progress['percent'] = 100;
        $progress['completed_at'] = now()->timestamp;

        if (!$success && $errorMessage) {
            $progress['error'] = $errorMessage;
            $progress['message'] = "Error: {$errorMessage}";
        } else {
            $progress['message'] = "Sync completed successfully";
        }

        Cache::put("jira_sync_progress_{$this->progressId}", $progress, now()->addHours(24));
    }

    /**
     * Add sync history to project integration
     */
    protected function addSyncHistory(Project $project, array $data): void
    {
        try {
            // Get integration
            $integration = Integration::where('type', Integration::TYPE_JIRA)->first();

            if ($integration) {
                $projectIntegration = ProjectIntegration::where('project_id', $project->id)
                    ->where('integration_id', $integration->id)
                    ->first();

                if ($projectIntegration) {
                    $config = $projectIntegration->project_specific_config;

                    // Add sync history entry
                    $config['sync_history'] = array_merge(
                        [$data], // New entry at the beginning
                        $config['sync_history'] ?? []
                    );

                    // Limit to 20 entries
                    if (count($config['sync_history']) > 20) {
                        $config['sync_history'] = array_slice($config['sync_history'], 0, 20);
                    }

                    $projectIntegration->project_specific_config = $config;
                    $projectIntegration->save();
                }
            }
        } catch (\Exception $e) {
            Log::error("Error adding sync history: " . $e->getMessage());
        }
    }
}
