<?php

namespace App\Http\Controllers;

use App\Jobs\LoadJiraProjectJob;
use App\Models\Integration;
use App\Models\OAuthState;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Epic;
use App\Models\Story;
use App\Models\Team;
use App\Services\JiraApiClient;
use App\Services\JiraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Cache;

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

        try {
            if ($this->isJiraConnectedForTeam($currentTeamId)) {
                $jiraConnected = true;
                $jiraService = new JiraService($currentTeamId);
                $jiraProjects = $jiraService->getProjects();
            }
        } catch (\Exception $e) {
            Log::error('Error getting Jira projects: ' . $e->getMessage());
        }

        $existingProjects = $team->projects()->get(['id', 'name']);

        return view('dashboard.integrations.jira-dashboard', compact('jiraConnected', 'jiraProjects', 'existingProjects', 'team'));
    }

    /**
     * Get Jira project details including epics, stories, and issues
     */
    public function getProjectDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'force_refresh' => 'nullable|boolean', // Add this line
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;
        $projectKey = $request->input('key');
        $forceRefresh = $request->input('force_refresh', false); // Add this line

        $cacheKey = "jira_project_{$currentTeamId}_{$projectKey}";
        if (!$forceRefresh && Cache::has($cacheKey)) {
            return $this->successResponse(Cache::get($cacheKey));
        }

        // Check if there's an active import job
        $progressId = $request->input('progress_id');
        if ($progressId && Cache::has("progress_{$progressId}")) {
            $progress = Cache::get("progress_{$progressId}");
            Log::info('Returning existing progress', [
                'progress_id' => $progressId,
                'percent' => $progress['percent'] ?? 0
            ]);

            return $this->successResponse([
                'loading' => true,
                'progress' => $progress
            ]);
        }

        // Start a new import job
        $job = new LoadJiraProjectJob($currentTeamId, $projectKey, Auth::id());
        $progressId = $job->getProgressId();

        Log::info('Starting new JIRA import job', [
            'project_key' => $projectKey,
            'progress_id' => $progressId
        ]);

        dispatch($job);

        // Return the progress ID so the frontend can track progress
        return $this->successResponse([
            'loading' => true,
            'progress' => [
                'id' => $progressId,
                'percent' => 0,
                'message' => 'Starting JIRA project import...'
            ]
        ]);
    }

    /**
     * Check progress of an import job
     */
    public function checkImportProgress(Request $request, $progressId)
    {
        Log::info('Progress check', [
            'progress_id' => $progressId,
            'found' => Cache::has("progress_{$progressId}")
        ]);

        $progress = Cache::get("progress_{$progressId}");

        if (!$progress) {
            // If no progress, initialize with zero progress
            $progress = [
                'percent' => 0,
                'message' => 'Initializing job...',
                'is_complete' => false,
                'is_success' => true,
                'updated_at' => now()->timestamp
            ];
            Cache::put("progress_{$progressId}", $progress, now()->addHour());

            Log::warning('Progress not found, creating initial progress', [
                'progress_id' => $progressId
            ]);
        }

        return $this->successResponse($progress);
    }

    /**
     * Directly load project data synchronously (no job)
     */
    public function loadProjectData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'progress_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $projectKey = $request->input('key');
        $progressId = $request->input('progress_id');
        $team = $this->getCurrentTeam($request);
        $teamId = $team->id;

        Log::info('Direct project loading started', [
            'project_key' => $projectKey,
            'progress_id' => $progressId
        ]);

        try {
            // Initialize progress
            Cache::put("progress_{$progressId}", [
                'percent' => 5,
                'message' => 'Starting Jira project data import',
                'is_complete' => false,
                'is_success' => true,
                'project_key' => $projectKey,
                'updated_at' => now()->timestamp
            ], now()->addHour());

            // Create service
            $jiraService = new JiraService($teamId);

            // Get project info - 15%
            Cache::put("progress_{$progressId}", [
                'percent' => 15,
                'message' => 'Fetching project information',
                'is_complete' => false,
                'is_success' => true,
                'project_key' => $projectKey,
                'updated_at' => now()->timestamp
            ], now()->addHour());

            $projects = $jiraService->getProjects();
            $project = collect($projects)->firstWhere('key', $projectKey);

            if (!$project) {
                throw new \Exception("Project not found: {$projectKey}");
            }

            // Prepare placeholder data - 30%
            Cache::put("progress_{$progressId}", [
                'percent' => 30,
                'message' => 'Preparing project structure',
                'is_complete' => false,
                'is_success' => true,
                'project_key' => $projectKey,
                'updated_at' => now()->timestamp
            ], now()->addHour());

            // For testing, create placeholder data
            $epics = [];
            $stories = [];
            $unassigned = [];

            for ($i = 1; $i <= 10; $i++) {
                $epics[] = [
                    'id' => "epic-{$i}",
                    'key' => "EPIC-{$i}",
                    'fields' => [
                        'summary' => "Epic {$i}",
                        'description' => "Description for Epic {$i}",
                        'issuetype' => ['name' => 'Epic'],
                        'status' => ['name' => 'Open']
                    ]
                ];
            }

            for ($i = 1; $i <= 20; $i++) {
                $stories[] = [
                    'id' => "story-{$i}",
                    'key' => "STORY-{$i}",
                    'fields' => [
                        'summary' => "Story {$i}",
                        'description' => "Description for Story {$i}",
                        'issuetype' => ['name' => 'Story'],
                        'status' => ['name' => 'To Do']
                    ]
                ];
            }

            for ($i = 1; $i <= 15; $i++) {
                $unassigned[] = [
                    'id' => "issue-{$i}",
                    'key' => "ISSUE-{$i}",
                    'fields' => [
                        'summary' => "Unassigned Issue {$i}",
                        'description' => "Description for Unassigned Issue {$i}",
                        'issuetype' => ['name' => rand(0, 1) ? 'Bug' : 'Task'],
                        'status' => ['name' => 'Open']
                    ]
                ];
            }

            // Cache results - 90%
            Cache::put("progress_{$progressId}", [
                'percent' => 90,
                'message' => 'Finalizing data',
                'is_complete' => false,
                'is_success' => true,
                'project_key' => $projectKey,
                'updated_at' => now()->timestamp
            ], now()->addHour());

            $result = [
                'project' => $project,
                'epics' => $epics,
                'stories' => $stories,
                'unassigned' => $unassigned,
                'metadata' => [
                    'total_issues' => count($epics) + count($stories) + count($unassigned),
                    'epic_count' => count($epics),
                    'story_count' => count($stories),
                    'unassigned_count' => count($unassigned),
                    'fetched_at' => now()->toIso8601String(),
                ]
            ];

            // Cache the project data
            Cache::put("jira_project_{$teamId}_{$projectKey}", $result, now()->addHours(1));

            // Set progress complete - 100%
            Cache::put("progress_{$progressId}", [
                'percent' => 100,
                'message' => 'Import complete',
                'is_complete' => true,
                'is_success' => true,
                'project_key' => $projectKey,
                'stats' => [
                    'total_issues' => count($epics) + count($stories) + count($unassigned),
                    'epics' => count($epics),
                    'stories' => count($stories),
                    'unassigned' => count($unassigned)
                ],
                'updated_at' => now()->timestamp
            ], now()->addHour());

            Log::info('Direct project loading complete', [
                'project_key' => $projectKey,
                'progress_id' => $progressId,
                'total_issues' => count($epics) + count($stories) + count($unassigned)
            ]);

            return $this->successResponse(['status' => 'complete']);
        } catch (\Exception $e) {
            Log::error('Direct project loading failed', [
                'project_key' => $projectKey,
                'progress_id' => $progressId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update progress with error
            Cache::put("progress_{$progressId}", [
                'percent' => 100,
                'message' => 'Error: ' . $e->getMessage(),
                'is_complete' => true,
                'is_success' => false,
                'project_key' => $projectKey,
                'updated_at' => now()->timestamp
            ], now()->addHour());

            return $this->errorResponse('Error loading project: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Import issues from Jira to a project
     */
    public function importIssues(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_key' => 'required|string',
            'create_new_project' => 'required|boolean',
            'arxitest_project_id' => 'required_if:create_new_project,false|uuid|exists:projects,id',
            'new_project_name' => 'required_if:create_new_project,true|string|max:255',
            'issues' => 'required|array',
            'issues.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        try {
            $jiraService = new JiraService($currentTeamId);
            $projectKey = $request->input('project_key');
            $issueKeys = $request->input('issues');
            $createNewProject = $request->input('create_new_project');

            // Create or get project
            if ($createNewProject) {
                $project = Project::create([
                    'name' => $request->input('new_project_name'),
                    'description' => "Imported from Jira project: {$projectKey}",
                    'team_id' => $currentTeamId,
                    'settings' => [
                        'jira_import' => [
                            'source' => $projectKey,
                            'date' => now()->toDateTimeString()
                        ]
                    ],
                ]);
            } else {
                $project = Project::findOrFail($request->input('arxitest_project_id'));
                if ($project->team_id !== $currentTeamId) {
                    return $this->errorResponse('You do not have access to this project.', 403);
                }
            }

            // Collect all issues to import
            $jql = 'key in (' . implode(',', $issueKeys) . ')';
            $fields = ['summary', 'description', 'issuetype', 'parent', 'status', 'created', 'updated', 'labels', 'priority', 'components'];
            $issues = $jiraService->getIssuesWithJql($jql, $fields);

            // Process the issues
            $results = $this->processJiraImport($issues, $project);

            return $this->successResponse([
                'project_id' => $project->id,
                'imported' => [
                    'epics' => $results['epicCount'],
                    'stories' => $results['storyCount'],
                    'test_cases' => $results['testCaseCount']
                ],
                'redirect' => route('dashboard.projects.show', $project->id)
            ], 'Import completed successfully');
        } catch (\Exception $e) {
            Log::error('Error importing Jira issues: ' . $e->getMessage());
            return $this->errorResponse('Failed to import issues: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Process import of Jira issues
     */
    private function processJiraImport(array $issues, Project $project): array
    {
        $epicCount = 0;
        $storyCount = 0;
        $testCaseCount = 0;
        $epicMap = [];
        $batchId = uniqid('import_');

        // First pass: Create epics
        foreach ($issues as $issue) {
            if ($issue['fields']['issuetype']['name'] === 'Epic') {
                $epicData = [
                    'project_id' => $project->id,
                    'name' => $issue['fields']['summary'],
                    'external_id' => $issue['key'],
                    'status' => $issue['fields']['status']['name'],
                ];

                $epic = Epic::updateOrCreate(
                    ['external_id' => $issue['key'], 'project_id' => $project->id],
                    $epicData
                );

                $epicMap[$issue['id']] = $epic->id;
                $epicCount++;
            }
        }

        // Second pass: Create stories and test cases
        foreach ($issues as $issue) {
            $issueType = $issue['fields']['issuetype']['name'];

            if (in_array($issueType, ['Story', 'Task', 'Bug'])) {
                // Create or update story
                $storyData = [
                    'project_id' => $project->id,
                    'title' => $issue['fields']['summary'],
                    'description' => $this->extractPlainTextFromAdf($issue['fields']['description'] ?? []),
                    'metadata' => [
                        'jira_id' => $issue['id'],
                        'jira_key' => $issue['key'],
                        'jira_issue_type' => $issueType,
                        'jira_status' => $issue['fields']['status']['name'],
                        'jira_labels' => $issue['fields']['labels'] ?? [],
                        'import_batch_id' => $batchId,
                        'import_timestamp' => now()->toIso8601String()
                    ],
                    'source' => 'jira',
                    'external_id' => $issue['key']
                ];

                // Associate with epic if available
                $epicLink = $issue['fields']['parent']['id'] ?? null;
                if ($epicLink && isset($epicMap[$epicLink])) {
                    $storyData['epic_id'] = $epicMap[$epicLink];
                }

                $story = Story::updateOrCreate(
                    ['external_id' => $issue['key'], 'source' => 'jira'],
                    $storyData
                );

                $storyCount++;

                // Create test case
                $testCaseData = [
                    'title' => "Verify: " . substr($issue['fields']['summary'], 0, 90),
                    'description' => $this->extractPlainTextFromAdf($issue['fields']['description'] ?? []),
                    'story_id' => $story->id,
                    'steps' => $this->generateTestSteps([], $issue['fields']['summary'], $issueType),
                    'expected_results' => $this->generateExpectedResults([], $issue['fields']['summary'], $issueType),
                    'priority' => $this->mapJiraPriorityToArxitest(
                        $issue['fields']['priority']['name'] ?? 'Medium'
                    ),
                    'status' => 'draft',
                    'tags' => array_merge(
                        ['jira-import', $issue['key'], strtolower($issueType)],
                        $issue['fields']['labels'] ?? []
                    )
                ];

                $testCase = \App\Models\TestCase::firstOrCreate(
                    [
                        'story_id' => $story->id,
                        'title' => "Verify: " . substr($issue['fields']['summary'], 0, 90)
                    ],
                    $testCaseData
                );

                $testCaseCount++;
            }
        }

        return [
            'epicCount' => $epicCount,
            'storyCount' => $storyCount,
            'testCaseCount' => $testCaseCount,
            'batchId' => $batchId
        ];
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
     * Extract plain text from Jira's Atlassian Document Format.
     */
    private function extractPlainTextFromAdf(array $doc): string
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

    /**
     * Generate test steps from available information and issue type.
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
     * Redirect to Jira authorization page
     */
    public function redirect(Request $request)
    {
        $userId = Auth::id();
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        $state = OAuthState::generateState($userId, $currentTeamId);

        $query = http_build_query([
            'audience'     => 'api.atlassian.com',
            'client_id'    => $this->jiraClient->getClientId(),
            'scope'        => 'read:jira-user read:jira-work write:jira-work offline_access',
            'redirect_uri' => $this->jiraClient->getRedirectUri(),
            'state'        => $state,
            'response_type' => 'code',
            'prompt'       => 'consent',
        ]);

        return redirect($this->jiraClient->getBaseUri() . '/authorize?' . $query);
    }

    /**
     * Handle the callback from Jira authorization
     */
    public function callback(Request $request)
    {
        $stateParam = $request->state;
        if (empty($stateParam)) {
            return redirect()->route('dashboard.integrations.index')->with('error', 'Invalid OAuth callback: missing state.');
        }

        $oauthState = OAuthState::where('state_token', $stateParam)
            ->where('expires_at', '>', now())
            ->first();

        if (!$oauthState) {
            return redirect()->route('dashboard.integrations.index')->with('error', 'OAuth state verification failed.');
        }

        $userId = $oauthState->user_id;
        $teamId = $oauthState->project_id;
        $oauthState->delete();

        try {
            // Exchange the code for an access token
            $tokenData = $this->jiraClient->exchangeCode($request->code);
            $resources = $this->jiraClient->getResources($tokenData['access_token']);
            $site = $resources[0];

            // Create or update the integration record
            $integration = Integration::firstOrCreate(
                ['type' => Integration::TYPE_JIRA],
                [
                    'name' => 'Jira',
                    'base_url' => $site['url'],
                    'is_active' => true
                ]
            );

            // Find a project to store the integration with
            $team = Team::find($teamId);
            $project = $team->projects()->first() ?? Project::create([
                'name' => $team->name . ' – Jira Credentials',
                'description' => 'Holds Jira OAuth tokens for the team.',
                'team_id' => $team->id,
                'settings' => ['is_placeholder' => true],
            ]);

            // Store encrypted credentials
            $credentials = [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => now()->addSeconds($tokenData['expires_in'])->timestamp,
                'cloud_id' => $site['id'],
                'site_url' => $site['url'],
                'created_at' => now()->timestamp,
            ];

            $encrypted = Crypt::encryptString(json_encode($credentials));

            // Update or create project integration
            ProjectIntegration::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'integration_id' => $integration->id
                ],
                [
                    'encrypted_credentials' => $encrypted,
                    'is_active' => true,
                    'project_specific_config' => [
                        'cloud_id' => $site['id'],
                        'site_url' => $site['url'],
                    ],
                ]
            );

            // Important - This block ensures the user is logged in when returning from OAuth
            if (!Auth::check()) {
                Auth::loginUsingId($userId, true);
                session(['current_team' => $teamId]);
                session()->save();
            }

            return redirect()->route('dashboard.integrations.jira.dashboard')
                ->with('success', 'Jira connected successfully! You can now browse and import issues.');
        } catch (\Exception $e) {
            Log::error('Jira OAuth error', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Jira authorization failed: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Jira integration
     */
    public function disconnect(Request $request)
    {
        $currentTeamId = session('current_team');
        if (! $currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Team context is missing.');
        }

        $jiraIntegration = Integration::where('type', Integration::TYPE_JIRA)->first();
        if (! $jiraIntegration) {
            return redirect()->route('dashboard.integrations.index')->with('info', 'No Jira integration config found.');
        }

        $deleted = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
            ->where('integration_id', $jiraIntegration->id)
            ->delete();

        if ($deleted) {
            return redirect()->route('dashboard.integrations.index')->with('success', 'Jira disconnected successfully.');
        }

        return redirect()->route('dashboard.integrations.index')->with('info', 'No active Jira connections to disconnect.');
    }
}
