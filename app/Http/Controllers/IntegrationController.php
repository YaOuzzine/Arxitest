<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\OAuthState;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Team;
use App\Models\Story as ArxitestStory;
use App\Models\TestCase as ArxitestTestCase;
use App\Models\TestSuite;
use App\Models\User;
use App\Services\JiraService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Contracts\Encryption\DecryptException;

class IntegrationController extends Controller
{
    /**
     * Display the integrations management view.
     */
    public function index(Request $request)
    {
        // Get current team context
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'Please select a team first.');
        }

        $team = Team::find($currentTeamId);
        if (!$team) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'Selected team not found.');
        }

        // Get optional project context
        $currentProjectId = $request->query('project_id', $team->projects()->value('id'));

        // Check for active Jira integration
        $jiraConnected = ProjectIntegration::whereHas('project', function ($q) use ($currentTeamId) {
            $q->where('team_id', $currentTeamId);
        })
            ->whereHas('integration', function ($q) {
                $q->where('type', Integration::TYPE_JIRA);
            })
            ->where('is_active', true)
            ->exists();

        // Check for GitHub integration (placeholder)
        $githubConnected = ProjectIntegration::whereHas('project', function ($q) use ($currentTeamId) {
            $q->where('team_id', $currentTeamId);
        })
            ->whereHas('integration', function ($q) {
                $q->where('type', Integration::TYPE_GITHUB);
            })
            ->where('is_active', true)
            ->exists();

        return view('dashboard.integrations.index', compact('jiraConnected', 'githubConnected', 'currentProjectId'));
    }

    /**
     * Initiate Jira OAuth flow by redirecting to Atlassian.
     */
    public function jiraRedirect(Request $request)
    {
        // Authentication check
        $userId = Auth::id();
        if (!$userId) {
            Log::error('Jira redirect attempted without authentication');
            return redirect()->route('login')
                ->with('error', 'You must be logged in to connect to Jira.');
        }

        // Get project context
        $targetProjectId = $request->query('target_project_id') ?? session('current_project_id');
        $currentTeamId = session('current_team');

        if (!$targetProjectId) {
            $team = Team::find($currentTeamId);
            $targetProjectId = $team?->projects()->value('id');
        }

        // Validation
        if (!$targetProjectId || !Project::where('id', $targetProjectId)
            ->where('team_id', $currentTeamId)
            ->exists()) {
            Log::warning('Invalid project for Jira integration', [
                'user_id' => $userId,
                'team_id' => $currentTeamId,
                'target_project_id' => $targetProjectId
            ]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Please select a valid project for this integration.');
        }

        // Generate state using database storage - no session required
        $state = OAuthState::generateState($userId, $targetProjectId);

        Log::debug('Generating OAuth state in database', [
            'state' => $state,
            'user_id' => $userId,
            'project_id' => $targetProjectId
        ]);

        // Build OAuth URL
        $query = http_build_query([
            'audience' => 'api.atlassian.com',
            'client_id' => config('services.atlassian.client_id'),
            'scope' => 'read:jira-user read:jira-work write:jira-work offline_access',
            'redirect_uri' => config('services.atlassian.redirect'),
            'state' => $state,
            'response_type' => 'code',
            'prompt' => 'consent',
        ]);

        Log::info('Redirecting to Atlassian for Jira OAuth', [
            'user_id' => $userId,
            'target_project_id' => $targetProjectId
        ]);

        return redirect('https://auth.atlassian.com/authorize?' . $query);
    }

    /**
     * Handle the callback from Atlassian after OAuth authorization.
     */
    public function jiraCallback(Request $request)
    {
        Log::info('Jira OAuth callback received');

        // Debug incoming request
        Log::debug('Callback received session state', [
            'session_id' => session()->getId(),
            'all_session_data' => session()->all(),
            'cookies' => $request->cookies->all(),
            'request_state' => $request->state
        ]);

        // Verify state parameter using database lookup
        $stateParam = $request->state;
        if (empty($stateParam)) {
            Log::error('Jira OAuth callback missing state parameter');
            return redirect()->route('login')
                ->with('error', 'Invalid OAuth callback. Missing state parameter.');
        }

        $oauthState = OAuthState::findValidState($stateParam);

        if (!$oauthState) {
            Log::error('Jira OAuth invalid or expired state token', [
                'state' => $stateParam
            ]);
            return redirect()->route('login')
                ->with('error', 'OAuth verification failed. Invalid or expired state token.');
        }

        // Get the stored user and project IDs
        $userId = $oauthState->user_id;
        $targetProjectId = $oauthState->project_id;

        Log::debug('Found valid OAuth state record', [
            'state_id' => $oauthState->id,
            'user_id' => $userId,
            'project_id' => $targetProjectId
        ]);

        // Check for errors from OAuth provider
        if ($request->has('error')) {
            Log::error('Jira OAuth returned an error', [
                'error' => $request->error,
                'description' => $request->error_description
            ]);

            // Clean up the state record
            $oauthState->delete();

            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Jira authorization failed: ' .
                    $request->input('error_description', $request->input('error', 'Unknown error')));
        }

        // Ensure we have stored context from the initial request
        if (!$targetProjectId || !$userId) {
            Log::error('Missing project ID or user ID in session', [
                'has_project_id' => !empty($targetProjectId),
                'has_user_id' => !empty($userId)
            ]);

            // Clear invalid session data
            $this->clearJiraSessionData($request);

            return redirect()->route('login')
                ->with('error', 'Your session expired during authorization. Please try again.');
        }

        // Get access token using authorization code
        try {
            $tokenResponse = Http::asForm()->post('https://auth.atlassian.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.atlassian.client_id'),
                'client_secret' => config('services.atlassian.client_secret'),
                'code' => $request->code,
                'redirect_uri' => config('services.atlassian.redirect'),
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('Failed to get Jira access token', [
                    'status' => $tokenResponse->status(),
                    'body' => $tokenResponse->body()
                ]);
                throw new \Exception('Failed to retrieve access token (' . $tokenResponse->status() . ')');
            }

            $tokenData = $tokenResponse->json();
            Log::info('Jira token obtained successfully');
        } catch (\Exception $e) {
            Log::error('Error exchanging code for token', [
                'error' => $e->getMessage()
            ]);

            $this->clearJiraSessionData($request);

            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Error obtaining Jira access token: ' . $e->getMessage());
        }

        // Get accessible Jira sites/resources
        try {
            $resourceResponse = Http::withToken($tokenData['access_token'])
                ->get('https://api.atlassian.com/oauth/token/accessible-resources');

            if (!$resourceResponse->successful() || empty($resourceResponse->json())) {
                Log::error('Failed to get Jira resources', [
                    'status' => $resourceResponse->status(),
                    'body' => $resourceResponse->body()
                ]);
                throw new \Exception('Could not retrieve Jira sites or none found for your account');
            }

            $resources = $resourceResponse->json();
            $jiraSite = $resources[0]; // Use first site

        } catch (\Exception $e) {
            Log::error('Error fetching Jira resources', [
                'error' => $e->getMessage()
            ]);

            $this->clearJiraSessionData($request);

            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Error accessing your Jira sites: ' . $e->getMessage());
        }

        // Ensure user is authenticated
        if (!Auth::check()) {
            try {
                $user = User::findOrFail($userId);
                Auth::login($user);
                $request->session()->regenerate();
                Log::info('User authenticated in Jira callback', ['user_id' => $userId]);
            } catch (\Exception $e) {
                Log::error('Failed to authenticate user', [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
                return redirect()->route('login')
                    ->with('error', 'Authentication error. Please log in and try again.');
            }
        }

        // Verify project exists and set team context
        $project = Project::find($targetProjectId);
        if (!$project) {
            Log::error('Target project not found', ['project_id' => $targetProjectId]);

            $this->clearJiraSessionData($request);

            return redirect()->route('dashboard.projects')
                ->with('error', 'The selected project no longer exists.');
        }

        // Set team context
        session(['current_team' => $project->team_id]);

        // Store the Jira credentials
        try {
            // Prepare credentials for storage
            $credentials = [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds($tokenData['expires_in'] - 60)->timestamp,
                'cloud_id' => $jiraSite['id'],
                'site_url' => $jiraSite['url'],
                'site_name' => $jiraSite['name'],
                'scopes' => explode(' ', $tokenData['scope'] ?? '')
            ];

            $encryptedCredentials = Crypt::encryptString(json_encode($credentials));

            // Get or create the Jira integration record
            $integration = Integration::firstOrCreate(
                ['type' => Integration::TYPE_JIRA],
                [
                    'name' => 'Jira',
                    'is_active' => true,
                    'base_url' => 'https://api.atlassian.com'
                ]
            );

            // Associate with the project
            ProjectIntegration::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'integration_id' => $integration->id
                ],
                [
                    'encrypted_credentials' => $encryptedCredentials,
                    'is_active' => true,
                    'project_specific_config' => [
                        'site_name' => $jiraSite['name'],
                        'site_url' => $jiraSite['url'],
                    ]
                ]
            );

            Log::info('Jira integration successfully configured', [
                'project_id' => $project->id,
                'jira_site' => $jiraSite['name']
            ]);
        } catch (DecryptException $e) {
            Log::error('Encryption error storing Jira credentials', [
                'error' => $e->getMessage()
            ]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Failed to securely store Jira connection (Encryption Error)');
        } catch (\Exception $e) {
            Log::error('Error storing Jira integration', [
                'error' => $e->getMessage()
            ]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Failed to save Jira connection: ' . $e->getMessage());
        }

        $oauthState->delete();

        // Success - redirect to integrations page
        return redirect()->route('dashboard.integrations.index')
            ->with('success', 'Jira connected successfully to project: ' . $project->name);
    }

    /**
     * Preview Jira import results (AJAX endpoint)
     */
    public function previewJiraImport(Request $request)
    {
        $validated = $request->validate([
            'jira_project_key' => 'required|string',
            'arxitest_project_id' => 'required|uuid|exists:projects,id',
            'issue_types' => 'required|array',
            'statuses' => 'nullable|array',
            'labels' => 'nullable|array',
            'custom_jql' => 'nullable|string',
            'mappings' => 'required|array',
            'mappings.epic_to_suite' => 'required|boolean',
            'mappings.create_default_suite' => 'required|boolean',
            'mappings.default_suite_id' => 'nullable|uuid|exists:test_suites,id',
            'sample_size' => 'nullable|integer|min:1|max:50'
        ]);

        try {
            $arxitestProject = Project::findOrFail($validated['arxitest_project_id']);
            $jiraService = new JiraService($arxitestProject);

            // Get sample of issues based on filters
            $sampleSize = $validated['sample_size'] ?? 10;
            $issues = $jiraService->getFilteredIssues([
                'projectKey' => $validated['jira_project_key'],
                'issueTypes' => $validated['issue_types'],
                'statuses' => $validated['statuses'] ?? [],
                'labels' => $validated['labels'] ?? [],
                'customJql' => $validated['custom_jql'] ?? '',
                'maxResults' => $sampleSize
            ]);

            // Mock what would be imported
            $mockImport = [
                'test_suites' => [],
                'test_cases' => [],
                'total_issues' => count($issues)
            ];

            // Process sample issues based on mappings
            foreach ($issues as $issue) {
                $issueType = $issue['fields']['issuetype']['name'] ?? '';

                if ($issueType === 'Epic' && $validated['mappings']['epic_to_suite']) {
                    $mockImport['test_suites'][] = [
                        'name' => $issue['fields']['summary'] ?? 'Untitled Epic',
                        'jira_key' => $issue['key'],
                        'jira_id' => $issue['id']
                    ];
                } else {
                    // Non-epic issues become test cases
                    $mockImport['test_cases'][] = [
                        'title' => $issue['fields']['summary'] ?? 'Untitled Issue',
                        'jira_key' => $issue['key'],
                        'jira_id' => $issue['id'],
                        'issue_type' => $issueType,
                        'parent_epic_key' => $issue['fields']['parent']['key'] ?? null
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'preview' => $mockImport
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect Jira integration.
     */
    public function jiraDisconnect(Request $request)
    {
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'Team context lost.');
        }

        $jiraIntegration = Integration::where('type', Integration::TYPE_JIRA)->first();
        if (!$jiraIntegration) {
            return redirect()->route('dashboard.integrations.index')
                ->with('info', 'Jira integration configuration not found.');
        }

        $projectIds = Project::where('team_id', $currentTeamId)->pluck('id');
        if ($projectIds->isEmpty()) {
            return redirect()->route('dashboard.integrations.index')
                ->with('info', 'No projects found to disconnect from Jira.');
        }

        $deletedCount = ProjectIntegration::whereIn('project_id', $projectIds)
            ->where('integration_id', $jiraIntegration->id)
            ->delete();

        if ($deletedCount > 0) {
            Log::info('Jira integrations disconnected', [
                'team_id' => $currentTeamId,
                'count' => $deletedCount
            ]);
            return redirect()->route('dashboard.integrations.index')
                ->with('success', 'Jira integration disconnected from all projects in this team.');
        }

        return redirect()->route('dashboard.integrations.index')
            ->with('info', 'No active Jira integrations found to disconnect.');
    }

    /**
     * Show enhanced options for importing from Jira
     */
    public function showJiraImportOptions(Request $request)
    {
        $arxitestProjectId = $request->query('project_id');
        if (!$arxitestProjectId) {
            return redirect()->route('dashboard.projects')
                ->with('error', 'Please select a project before importing.');
        }

        $arxitestProject = Project::find($arxitestProjectId);
        if (!$arxitestProject || $arxitestProject->team_id !== session('current_team')) {
            return redirect()->route('dashboard.projects')
                ->with('error', 'You do not have access to this project.');
        }

        try {
            $jiraService = new JiraService($arxitestProject);
            $jiraProjects = $jiraService->getProjects();

            // Get test suites for the target project for mapping options
            $testSuites = $arxitestProject->testSuites()->get(['id', 'name']);

            // Get existing mappings if any
            $existingMappings = ProjectIntegration::where('project_id', $arxitestProjectId)
                ->whereHas('integration', function ($q) {
                    $q->where('type', Integration::TYPE_JIRA);
                })
                ->value('project_specific_config')['mappings'] ?? [];

            return view('dashboard.integrations.jira-import', [
                'jiraProjects' => $jiraProjects,
                'arxitestProjectId' => $arxitestProject->id,
                'arxitestProjectName' => $arxitestProject->name,
                'testSuites' => $testSuites,
                'existingMappings' => $existingMappings
            ]);
        } catch (\Exception $e) {
            return redirect()->route('dashboard.integrations.index', ['project_id' => $arxitestProjectId])
                ->with('error', 'Could not access Jira projects: ' . $e->getMessage());
        }
    }

    /**
     * Import Jira project with advanced options
     */
    public function importJiraProject(Request $request)
    {
        $validated = $request->validate([
            'jira_project_key' => 'required|string',
            'jira_project_name' => 'required|string',
            'arxitest_project_id' => 'required|uuid|exists:projects,id',
            'issue_types' => 'required|array',
            'statuses' => 'nullable|array',
            'labels' => 'nullable|array',
            'custom_jql' => 'nullable|string',
            'mappings' => 'required|array',
            'mappings.epic_to_suite' => 'required|boolean',
            'mappings.create_default_suite' => 'required|boolean',
            'mappings.default_suite_id' => 'nullable|uuid|exists:test_suites,id',
            'mappings.include_description' => 'required|boolean',
            'mappings.status_to_priority' => 'nullable|array'
        ]);

        $jiraProjectKey = $validated['jira_project_key'];
        $jiraProjectName = $validated['jira_project_name'];
        $arxitestProjectId = $validated['arxitest_project_id'];

        $arxitestProject = Project::findOrFail($arxitestProjectId);
        if ($arxitestProject->team_id !== session('current_team')) {
            return back()->with('error', 'You do not have permission to import into this project.');
        }

        DB::beginTransaction();
        try {
            $jiraService = new JiraService($arxitestProject);

            // Get issues based on filters
            $issues = $jiraService->getFilteredIssues([
                'projectKey' => $jiraProjectKey,
                'issueTypes' => $validated['issue_types'],
                'statuses' => $validated['statuses'] ?? [],
                'labels' => $validated['labels'] ?? [],
                'customJql' => $validated['custom_jql'] ?? '',
            ]);

            // Track import progress
            $epicToSuiteMap = [];
            $storiesCreated = 0;
            $testCasesCreated = 0;
            $defaultSuite = null;

            // Save mappings in project integration config
            $projectIntegration = ProjectIntegration::where('project_id', $arxitestProjectId)
                ->whereHas('integration', function ($q) {
                    $q->where('type', Integration::TYPE_JIRA);
                })
                ->first();

            if ($projectIntegration) {
                $config = $projectIntegration->project_specific_config ?? [];
                $config['mappings'] = $validated['mappings'];
                $projectIntegration->update(['project_specific_config' => $config]);
            }

            // Process epics first if mapping to suites
            if ($validated['mappings']['epic_to_suite']) {
                foreach ($issues as $index => $issue) {
                    if (($issue['fields']['issuetype']['name'] ?? '') === 'Epic') {
                        $epicName = $issue['fields']['summary'] ?? 'Untitled Epic ' . $issue['key'];
                        $suiteSettings = [
                            'jira_epic_id' => $issue['id'],
                            'jira_epic_key' => $issue['key']
                        ];

                        $testSuite = TestSuite::updateOrCreate(
                            [
                                'project_id' => $arxitestProject->id,
                                'settings->jira_epic_key' => $issue['key']
                            ],
                            [
                                'name' => $epicName,
                                'description' => $validated['mappings']['include_description'] ?
                                    ($issue['fields']['description'] ?? '') : 'From Epic: ' . $issue['key'],
                                'settings' => $suiteSettings
                            ]
                        );

                        $epicToSuiteMap[$issue['id']] = $testSuite->id;

                        // Remove processed epics
                        unset($issues[$index]);
                    }
                }

                // Reset array keys
                $issues = array_values($issues);
            }

            // Create default suite if needed
            if ($validated['mappings']['create_default_suite']) {
                if (!empty($validated['mappings']['default_suite_id'])) {
                    $defaultSuite = TestSuite::find($validated['mappings']['default_suite_id']);
                }

                if (!$defaultSuite) {
                    $defaultSuite = TestSuite::firstOrCreate(
                        [
                            'project_id' => $arxitestProject->id,
                            'name' => 'Imported from Jira'
                        ],
                        [
                            'description' => 'Issues imported from Jira project: ' . $jiraProjectName,
                            'settings' => ['jira_project_key' => $jiraProjectKey]
                        ]
                    );
                }
            }

            // Process remaining issues (stories, tasks, etc.)
            foreach ($issues as $issue) {
                $issueTitle = $issue['fields']['summary'] ?? 'Untitled Issue ' . $issue['key'];
                $issueDescription = $validated['mappings']['include_description'] ?
                    ($issue['fields']['description'] ?? '') : '';
                $parentEpicId = $issue['fields']['parent']['id'] ?? null;
                $issueType = $issue['fields']['issuetype']['name'] ?? 'Unknown';

                // Map issue priority based on status if configured
                $issuePriority = 'medium'; // Default
                $issueStatus = $issue['fields']['status']['name'] ?? '';

                if (!empty($validated['mappings']['status_to_priority']) && isset($validated['mappings']['status_to_priority'][$issueStatus])) {
                    $issuePriority = $validated['mappings']['status_to_priority'][$issueStatus];
                }

                // Determine suite to associate with
                $suiteId = null;
                if ($parentEpicId && isset($epicToSuiteMap[$parentEpicId])) {
                    $suiteId = $epicToSuiteMap[$parentEpicId];
                } elseif ($defaultSuite) {
                    $suiteId = $defaultSuite->id;
                } else {
                    // Skip if no suite to associate with
                    continue;
                }

                // Create the story
                $arxitestStory = ArxitestStory::updateOrCreate(
                    [
                        'external_id' => $issue['key'],
                        'source' => 'jira'
                    ],
                    [
                        'title' => $issueTitle,
                        'description' => $issueDescription,
                        'metadata' => [
                            'jira_id' => $issue['id'],
                            'jira_status' => $issueStatus,
                            'jira_type' => $issueType,
                            'jira_labels' => $issue['fields']['labels'] ?? [],
                            'jira_priority' => $issue['fields']['priority']['name'] ?? null
                        ]
                    ]
                );
                $storiesCreated++;

                // Create test case
                ArxitestTestCase::firstOrCreate(
                    [
                        'story_id' => $arxitestStory->id,
                        'suite_id' => $suiteId,
                        'title' => "[$issueType] " . Str::limit($issueTitle, 100)
                    ],
                    [
                        'steps' => [
                            'Navigate to relevant feature',
                            'Verify functionality described in: ' . $issue['key'] . ' (' . $issueType . ')'
                        ],
                        'expected_results' => 'Feature works as described in ' . $issueType . '.',
                        'priority' => $issuePriority,
                        'status' => 'draft',
                        'tags' => array_merge([$issueType], $issue['fields']['labels'] ?? [])
                    ]
                );
                $testCasesCreated++;
            }

            DB::commit();

            return redirect()->route('dashboard.projects.show', $arxitestProject->id)
                ->with('success', "Imported {$storiesCreated} issues and created {$testCasesCreated} test cases from '{$jiraProjectName}'.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Jira import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('integrations.jira.import.options', ['project_id' => $arxitestProjectId])
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Get issue types, statuses, and labels for a Jira project (AJAX endpoint)
     */
    public function getJiraProjectMetadata(Request $request)
    {
        $validated = $request->validate([
            'jira_project_key' => 'required|string',
            'arxitest_project_id' => 'required|uuid|exists:projects,id'
        ]);

        try {
            $arxitestProject = Project::findOrFail($validated['arxitest_project_id']);
            $jiraService = new JiraService($arxitestProject);

            // Get issue types
            $issueTypes = $jiraService->getIssueTypes($validated['jira_project_key']);

            // Get sample issues to extract statuses and labels
            $sampleIssues = $jiraService->getFilteredIssues([
                'projectKey' => $validated['jira_project_key'],
                'maxResults' => 100,
                'fields' => ['status', 'labels']
            ]);

            // Extract unique statuses and labels
            $statuses = [];
            $labels = [];

            foreach ($sampleIssues as $issue) {
                $status = $issue['fields']['status']['name'] ?? null;
                if ($status && !in_array($status, $statuses)) {
                    $statuses[] = $status;
                }

                $issueLabels = $issue['fields']['labels'] ?? [];
                foreach ($issueLabels as $label) {
                    if (!in_array($label, $labels)) {
                        $labels[] = $label;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'issueTypes' => $issueTypes,
                'statuses' => $statuses,
                'labels' => $labels
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Clear Jira OAuth session data.
     */
    private function clearJiraSessionData(Request $request)
    {
        $request->session()->forget([
            'jira_oauth_state',
            'jira_target_project_id',
            'jira_user_id'
        ]);
    }
}
