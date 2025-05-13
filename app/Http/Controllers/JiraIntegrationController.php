<?php

namespace App\Http\Controllers;

use App\Jobs\JiraSyncJob;
use App\Models\Integration;
use App\Models\OAuthState;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Story;
use App\Models\Team;
use App\Services\JiraApiClient;
use App\Services\JiraService;
use App\Services\JiraSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Traits\JsonResponse;

class JiraIntegrationController extends Controller
{
    use JsonResponse;

    protected JiraApiClient $jiraClient;

    public function __construct(JiraApiClient $jiraClient)
    {
        $this->jiraClient = $jiraClient;
    }

    /**
     * Show the Jira integration dashboard
     */
    public function dashboard(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        $jiraConnected = false;
        $jiraProjects = [];
        $projectIntegration = null;
        $lastSync = null;
        $syncHistory = [];

        try {
            if ($this->isJiraConnectedForTeam($currentTeamId)) {
                $jiraConnected = true;

                // Get the integration
                $projectIntegration = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
                    ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
                    ->where('is_active', true)
                    ->first();

                // Get Jira service and projects
                $jiraService = new JiraService($currentTeamId);
                $jiraProjects = $jiraService->getProjects();

                // Get sync history
                $syncHistoryData = $projectIntegration->project_specific_config['sync_history'] ?? [];
                $syncHistory = array_slice($syncHistoryData, 0, 10); // Last 10 syncs

                // Find last sync
                if (!empty($syncHistory)) {
                    $lastSync = $syncHistory[0];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting Jira projects: ' . $e->getMessage());
        }

        $existingProjects = $team->projects()->get(['id', 'name']);

        return view('dashboard.integrations.jira-dashboard', compact(
            'jiraConnected',
            'jiraProjects',
            'existingProjects',
            'team',
            'projectIntegration',
            'lastSync',
            'syncHistory'
        ));
    }

    /**
     * Configure Jira settings for a project
     */
    public function configure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'jira_project_key' => 'required|string',
            'sync_settings' => 'array',
            'sync_settings.auto_sync' => 'boolean',
            'sync_settings.sync_interval' => 'integer|min:15',
            'sync_settings.sync_test_cases' => 'boolean',
            'sync_settings.sync_comments' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        try {
            // Find the project
            $project = Project::findOrFail($request->input('project_id'));

            // Ensure it belongs to the current team
            if ($project->team_id !== $currentTeamId) {
                return $this->errorResponse('Project does not belong to your team', 403);
            }

            // Update project settings
            $settings = $project->settings ?? [];
            $settings['jira_project_key'] = $request->input('jira_project_key');
            $settings['jira_sync_settings'] = $request->input('sync_settings');

            $project->settings = $settings;
            $project->save();

            // Update the integration settings
            $integration = Integration::where('type', Integration::TYPE_JIRA)->first();

            if ($integration) {
                $projectIntegration = ProjectIntegration::where('project_id', $project->id)
                    ->where('integration_id', $integration->id)
                    ->first();

                if (!$projectIntegration) {
                    // Find an integration from another project in the team
                    $teamIntegration = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
                        ->where('integration_id', $integration->id)
                        ->where('is_active', true)
                        ->first();

                    if ($teamIntegration) {
                        // Create a new integration for this project with the same credentials
                        $projectIntegration = ProjectIntegration::create([
                            'project_id' => $project->id,
                            'integration_id' => $integration->id,
                            'encrypted_credentials' => $teamIntegration->encrypted_credentials,
                            'is_active' => true,
                            'project_specific_config' => [
                                'jira_project_key' => $request->input('jira_project_key'),
                                'sync_settings' => $request->input('sync_settings'),
                                'sync_history' => []
                            ]
                        ]);
                    }
                } else {
                    // Update existing
                    $config = $projectIntegration->project_specific_config;
                    $config['jira_project_key'] = $request->input('jira_project_key');
                    $config['sync_settings'] = $request->input('sync_settings');

                    $projectIntegration->project_specific_config = $config;
                    $projectIntegration->save();
                }
            }

            return $this->successResponse([
                'project_id' => $project->id,
                'jira_project_key' => $request->input('jira_project_key'),
                'sync_settings' => $request->input('sync_settings')
            ], 'Jira integration configured successfully');

        } catch (\Exception $e) {
            Log::error('Error configuring Jira integration: ' . $e->getMessage());
            return $this->errorResponse('Failed to configure Jira integration: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Start a sync job
     */
    public function startSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'direction' => 'required|in:pull,push,both',
            'entity_types' => 'required|array',
            'entity_types.*' => 'required|in:story,test_case,test_suite',
            'sync_options' => 'array'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        try {
            $project = Project::findOrFail($request->input('project_id'));

            // Ensure it belongs to the current team
            if ($project->team_id !== $currentTeamId) {
                return $this->errorResponse('Project does not belong to your team', 403);
            }

            // Get the Jira project key
            $jiraProjectKey = $project->settings['jira_project_key'] ?? null;

            if (!$jiraProjectKey) {
                return $this->errorResponse('Jira project key not configured for this project', 400);
            }

            // Create and dispatch the sync job
            $syncJob = new JiraSyncJob(
                $project->id,
                $request->input('direction'),
                $request->input('entity_types'),
                $request->input('sync_options', [])
            );

            $progressId = $syncJob->getProgressId();

            dispatch($syncJob);

            return $this->successResponse([
                'progress_id' => $progressId,
                'project_id' => $project->id,
                'direction' => $request->input('direction'),
                'entity_types' => $request->input('entity_types')
            ], 'Jira sync job started');

        } catch (\Exception $e) {
            Log::error('Error starting Jira sync: ' . $e->getMessage());
            return $this->errorResponse('Failed to start Jira sync: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get the current sync status
     */
    public function syncStatus(Request $request, string $progressId)
    {
        $progressData = Cache::get("jira_sync_progress_{$progressId}");

        if (!$progressData) {
            return $this->errorResponse('Sync progress not found', 404);
        }

        // Add additional info
        if (isset($progressData['started_at'])) {
            $startTime = $progressData['started_at'];
            $endTime = $progressData['completed_at'] ?? now()->timestamp;
            $progressData['elapsed_seconds'] = $endTime - $startTime;
            $progressData['elapsed_time'] = $this->formatElapsedTime($progressData['elapsed_seconds']);
        }

        return $this->successResponse($progressData);
    }

    /**
     * Get categorization options for Jira issues
     */
    public function getCategorizationOptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'jira_project_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        try {
            $project = Project::findOrFail($request->input('project_id'));

            // Ensure it belongs to the current team
            if ($project->team_id !== $currentTeamId) {
                return $this->errorResponse('Project does not belong to your team', 403);
            }

            // Get jira service
            $jiraService = new JiraService($currentTeamId);

            // Get issue types
            $createMeta = $jiraService->getCreateMeta(
                $request->input('jira_project_key'),
                ''  // Empty to get all issue types
            );

            // Extract info
            $issueTypes = [];
            $fields = [];

            if (isset($createMeta['projects'][0]['issuetypes'])) {
                foreach ($createMeta['projects'][0]['issuetypes'] as $issueType) {
                    $issueTypes[] = [
                        'id' => $issueType['id'],
                        'name' => $issueType['name'],
                        'description' => $issueType['description'],
                        'icon' => $issueType['iconUrl']
                    ];

                    // Get common fields
                    if (empty($fields) && isset($issueType['fields'])) {
                        foreach ($issueType['fields'] as $fieldId => $field) {
                            $fields[] = [
                                'id' => $fieldId,
                                'name' => $field['name'],
                                'required' => $field['required'] ?? false,
                                'type' => $field['schema']['type'] ?? 'string'
                            ];
                        }
                    }
                }
            }

            // Get statuses
            $statuses = [];

            // Return the data
            return $this->successResponse([
                'issue_types' => $issueTypes,
                'fields' => $fields,
                'statuses' => $statuses
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting categorization options: ' . $e->getMessage());
            return $this->errorResponse('Failed to get categorization options: ' . $e->getMessage(), 500);
        }
    }

    // Keep existing methods like redirect(), callback(), disconnect(), etc.

    /**
     * Format elapsed seconds into a human-readable string
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
}
