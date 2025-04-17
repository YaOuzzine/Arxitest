<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Team;
use App\Models\TestSuite;
use App\Models\Story as ArxitestStory;
use App\Models\TestCase as ArxitestTestCase;
use App\Models\User; // Import User model
use App\Services\JiraService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Encryption\DecryptException; // Import DecryptException

class IntegrationController extends Controller
{
    /**
     * Display the integrations management view.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // This method requires the user to be authenticated via middleware
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Please select a team first.');
        }

        $team = Team::find($currentTeamId);
        if (!$team) {
             return redirect()->route('dashboard.select-team')->with('error', 'Selected team not found.');
        }

        $currentProjectId = $request->query('project_id', $team->projects()->value('id')); // Get project context if provided, else default

        // Check Jira connection status for the team
        $jiraConnected = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->exists();

        // Check GitHub connection status (Placeholder)
        $githubConnected = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_GITHUB))
            ->where('is_active', true)
            ->exists();

        return view('dashboard.integrations.index', compact('jiraConnected', 'githubConnected', 'currentProjectId'));
    }

    // --- JIRA INTEGRATION ---

    /**
     * Redirect the user to the Atlassian authorization page.
     * Stores necessary context in the session.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function jiraRedirect(Request $request)
    {
        // This route MUST be protected by auth middleware ('web', 'auth:web', 'require.team')

        $targetProjectId = $request->query('target_project_id', session('current_project_id')); // Get from query or maybe session
        $currentTeamId = session('current_team');
        $userId = Auth::id(); // Get the initiating user's ID

        if (!$targetProjectId) {
             // If still no target project, try the first project of the current team
             $team = Team::find($currentTeamId);
             $targetProjectId = $team?->projects()->value('id');
        }

        // Validate that the target project exists and belongs to the current team
        if (!$targetProjectId || !Project::where('id', $targetProjectId)->where('team_id', $currentTeamId)->exists()) {
             Log::warning('Jira redirect initiated without a valid target project for the current team.', ['user_id' => $userId, 'team_id' => $currentTeamId, 'target_project_id' => $targetProjectId]);
             return redirect()->route('dashboard.integrations.index')
                 ->with('error', 'Cannot initiate Jira connection without a valid project context for your current team.');
        }

        $state = Str::random(40);

        // Store state, target project ID, and the initiating user ID in the session
        $request->session()->put('oauth_state', $state);
        $request->session()->put('jira_target_project_id', $targetProjectId);
        $request->session()->put('jira_initiating_user_id', $userId); // Store the user ID

        $query = http_build_query([
            'audience' => 'api.atlassian.com',
            'client_id' => config('services.atlassian.client_id'),
            'scope' => implode(' ', [
                'read:jira-user', 'read:jira-work', 'write:jira-work', 'offline_access',
            ]),
            'redirect_uri' => config('services.atlassian.redirect'),
            'state' => $state,
            'response_type' => 'code',
            'prompt' => 'consent',
        ]);

        Log::info('Redirecting to Atlassian for Jira OAuth.', ['user_id' => $userId, 'target_project_id' => $targetProjectId]);
        return redirect('https://auth.atlassian.com/authorize?' . $query);
    }

    /**
     * Handle the callback from Atlassian after authorization.
     * Uses the "re-login" strategy.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function jiraCallback(Request $request)
    {
        Log::info('Jira OAuth callback received.');

        // --- 1. Retrieve State and Context from Session ---
        $state = $request->session()->pull('oauth_state');
        $targetProjectId = $request->session()->pull('jira_target_project_id');
        $initiatingUserId = $request->session()->pull('jira_initiating_user_id'); // Get the user ID

        Log::debug('Jira Callback Session State:', ['session_id' => $request->session()->getId(), 'retrieved_state' => !empty($state), 'retrieved_project_id' => !empty($targetProjectId), 'retrieved_user_id' => !empty($initiatingUserId)]);

        // --- 2. Validate State ---
        if (empty($state) || !$request->has('state') || $state !== $request->state) {
            Log::error('Jira OAuth callback state mismatch or missing.', ['state_in_session' => !empty($state), 'state_in_request' => $request->has('state')]);
            return redirect()->route('login')->with('error', 'OAuth security validation failed. Please try connecting again.'); // Redirect to login might be safer
        }
        Log::info('Jira OAuth callback state matched.');

        // --- 3. Validate Context Retrieved from Session ---
        if (!$initiatingUserId || !$targetProjectId) {
            Log::error('Jira OAuth callback missing user or project context from session.', ['user_id' => $initiatingUserId, 'project_id' => $targetProjectId]);
            return redirect()->route('login')->with('error', 'Your session context was lost during the Jira connection. Please log in and try again.');
        }

        // --- 4. Handle Potential Errors from Atlassian ---
        if ($request->has('error')) {
            Log::error('Jira OAuth callback error from Atlassian.', ['error' => $request->error, 'description' => $request->error_description]);
            // Log the user in before redirecting with error, so they land on dashboard
            Auth::loginUsingId($initiatingUserId);
            $request->session()->regenerate(); // Regenerate session after login
             // We might not have team context here, maybe redirect to select-team?
            return redirect()->route('dashboard.integrations.index')->with('error', 'Jira authorization failed: ' . $request->input('error_description', $request->input('error', 'Unknown error')));
        }

        // --- 5. Exchange Code for Token ---
        try {
            $tokenResponse = Http::asForm()->post('https://auth.atlassian.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.atlassian.client_id'),
                'client_secret' => config('services.atlassian.client_secret'),
                'code' => $request->code,
                'redirect_uri' => config('services.atlassian.redirect'),
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('Failed to get Jira access token.', ['status' => $tokenResponse->status(), 'body' => $tokenResponse->body()]);
                throw new \Exception('Could not retrieve access token from Jira (' . $tokenResponse->status() . ').');
            }
            $tokenData = $tokenResponse->json();
            Log::info('Jira token data received successfully.', ['scopes' => $tokenData['scope'] ?? 'N/A']);

        } catch (\Exception $e) {
            // Log the user in before redirecting with error
            Auth::loginUsingId($initiatingUserId);
            $request->session()->regenerate();
            return redirect()->route('dashboard.integrations.index')->with('error', 'Error exchanging code for token: ' . $e->getMessage());
        }

        // --- 6. Get Accessible Resources ---
        try {
            $resourceResponse = Http::withToken($tokenData['access_token'])
                ->get('https://api.atlassian.com/oauth/token/accessible-resources');

            if (!$resourceResponse->successful() || empty($resourceResponse->json())) {
                Log::error('Failed to get Jira accessible resources or none found.', ['status' => $resourceResponse->status(), 'body' => $resourceResponse->body()]);
                throw new \Exception('Could not retrieve accessible Jira sites or none were found associated with your account.');
            }
            $resources = $resourceResponse->json();
            $jiraSite = $resources[0]; // Use the first site

        } catch (\Exception $e) {
             // Log the user in before redirecting with error
            Auth::loginUsingId($initiatingUserId);
            $request->session()->regenerate();
            return redirect()->route('dashboard.integrations.index')->with('error', 'Error fetching Jira resources: ' . $e->getMessage());
        }

        // --- 7. Log the User Back In Explicitly ---
        Log::debug('Attempting to log user back in.', ['user_id' => $initiatingUserId]);
        $user = User::find($initiatingUserId);
        if (!$user) {
            Log::error('Initiating user not found during Jira callback.', ['user_id' => $initiatingUserId]);
            return redirect()->route('login')->with('error', 'Could not find your user account. Please contact support.');
        }
        Auth::guard('web')->login($user, true); // Log in the user using the 'web' guard, true for 'remember'
        $request->session()->regenerate(); // Regenerate session ID after login for security
        Log::info('User explicitly logged back in after Jira callback.', ['user_id' => Auth::id()]);

        // --- 8. Verify Project Ownership (Now that user is logged in) ---
        $arxitestProject = Project::find($targetProjectId);
        if (!$arxitestProject) {
            Log::error('Target Arxitest project not found after re-login.', ['project_id' => $targetProjectId]);
            return redirect()->route('dashboard.integrations.index')->with('error', 'Target project could not be found.');
        }

        // Re-fetch current team from session *after* login and regeneration
        $currentTeamId = session('current_team');
        // If current_team isn't set (maybe lost during redirect/login), set it based on the project
        if (!$currentTeamId || $arxitestProject->team_id !== $currentTeamId) {
             Log::warning('Session current_team mismatch or missing after re-login. Setting based on target project.', ['user_id' => Auth::id(), 'target_project_id' => $targetProjectId, 'project_team_id' => $arxitestProject->team_id, 'session_team_id' => $currentTeamId]);
             session(['current_team' => $arxitestProject->team_id]); // Set the team context
             // Optionally check if the user belongs to this team
             if (!$user->teams()->where('team_id', $arxitestProject->team_id)->exists()) {
                  Log::error('User does not belong to the target project\'s team.', ['user_id' => Auth::id(), 'project_id' => $targetProjectId, 'team_id' => $arxitestProject->team_id]);
                  Auth::logout(); // Log out as a safety measure
                  $request->session()->invalidate();
                  $request->session()->regenerateToken();
                  return redirect()->route('login')->with('error', 'Permission error linking Jira. Please log in again.');
             }
        }
        Log::info('Project ownership verified post-login.', ['project_id' => $targetProjectId, 'team_id' => $arxitestProject->team_id]);


        // --- 9. Store Credentials ---
         try {
            $credentials = [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds($tokenData['expires_in'] - 60)->timestamp,
                'cloud_id' => $jiraSite['id'],
                'site_url' => $jiraSite['url'],
                'site_name' => $jiraSite['name'],
                'scopes' => $jiraSite['scopes'],
            ];
            $encryptedCredentials = Crypt::encryptString(json_encode($credentials));

            $integration = Integration::firstOrCreate(
                ['type' => Integration::TYPE_JIRA],
                ['name' => 'Jira', 'is_active' => true, 'base_url' => 'https://api.atlassian.com']
            );

            ProjectIntegration::updateOrCreate(
                ['project_id' => $arxitestProject->id, 'integration_id' => $integration->id],
                [
                    'encrypted_credentials' => $encryptedCredentials,
                    'is_active' => true,
                    'project_specific_config' => json_encode([
                         'site_name' => $jiraSite['name'], 'site_url' => $jiraSite['url'],
                    ])
                ]
            );

            Log::info('Jira integration successfully configured for project.', ['project_id' => $arxitestProject->id, 'jira_site' => $jiraSite['name'], 'user_id' => Auth::id()]);

        } catch (DecryptException $e) {
             Log::error('Encryption failed while storing Jira credentials.', ['project_id' => $arxitestProject->id, 'error' => $e->getMessage()]);
             return redirect()->route('dashboard.integrations.index')->with('error', 'Failed to securely store Jira connection details (Encryption Error).');
        } catch (\Exception $e) {
             Log::error('Failed to store Jira integration credentials.', ['project_id' => $arxitestProject->id, 'error' => $e->getMessage()]);
             return redirect()->route('dashboard.integrations.index')->with('error', 'Failed to save Jira connection details.');
        }

        // --- 10. Redirect on Success ---
        // User is now logged in, session regenerated, integration stored.
        return redirect()->route('dashboard.integrations.index') // Or maybe project integrations settings page
            ->with('success', 'Jira connected successfully to project: ' . $arxitestProject->name);
    }

    /**
     * Disconnect Jira integration.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function jiraDisconnect(Request $request)
    {
        // This route MUST be protected by auth middleware ('web', 'auth:web', 'require.team')
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Team context lost.');
        }

        $jiraIntegration = Integration::where('type', Integration::TYPE_JIRA)->first();

        if ($jiraIntegration) {
            $projectIds = Project::where('team_id', $currentTeamId)->pluck('id');

            if ($projectIds->isNotEmpty()) {
                $deletedCount = ProjectIntegration::whereIn('project_id', $projectIds)
                    ->where('integration_id', $jiraIntegration->id)
                    ->delete();

                if ($deletedCount > 0) {
                    Log::info('Jira integrations disconnected for team.', ['team_id' => $currentTeamId, 'deleted_count' => $deletedCount, 'user_id' => Auth::id()]);
                    return redirect()->route('dashboard.integrations.index')
                        ->with('success', 'Jira integration disconnected successfully from all projects in this team.');
                }
            }
            Log::warning('No active Jira integration found to disconnect for team.', ['team_id' => $currentTeamId, 'user_id' => Auth::id()]);
            return redirect()->route('dashboard.integrations.index')
                ->with('info', 'No active Jira integration found to disconnect for this team.');
        }

        return redirect()->route('dashboard.integrations.index')
            ->with('info', 'Jira integration configuration not found.');
    }


    /**
     * Show options for importing from Jira.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showJiraImportOptions(Request $request)
    {
        // This route MUST be protected by auth middleware ('web', 'auth:web', 'require.team')
        $arxitestProjectId = $request->query('project_id');
        if (!$arxitestProjectId) {
             return redirect()->route('dashboard.projects')->with('error', 'Please select an Arxitest project context before importing.');
        }

        $arxitestProject = Project::find($arxitestProjectId);
        // Verify ownership against the *now authenticated* user's team context
        if (!$arxitestProject || $arxitestProject->team_id !== session('current_team')) {
              Log::warning('Attempt to access Jira import options for invalid/unauthorized project.', ['user_id' => Auth::id(), 'target_project_id' => $arxitestProjectId, 'current_team_id' => session('current_team')]);
              return redirect()->route('dashboard.projects')->with('error', 'Invalid or inaccessible Arxitest project selected.');
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
            Log::error('Error fetching Jira projects for import options: ' . $e->getMessage(), ['arxitest_project_id' => $arxitestProjectId, 'user_id' => Auth::id()]);
            return redirect()->route('dashboard.integrations.index', ['project_id' => $arxitestProjectId]) // Pass project ID back if possible
                ->with('error', 'Could not list Jira projects: ' . $e->getMessage() . '. Please ensure Jira is connected to project "' . $arxitestProject->name . '".');
        }
    }

    /**
     * Handle the import of a selected Jira project into the specified Arxitest project.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importJiraProject(Request $request)
    {
        // This route MUST be protected by auth middleware ('web', 'auth:web', 'require.team')
        $validated = $request->validate([
            'jira_project_key' => 'required|string|max:100',
            'jira_project_name' => 'required|string|max:255',
            'arxitest_project_id' => 'required|uuid|exists:projects,id'
        ]);

        $jiraProjectKey = $validated['jira_project_key'];
        $jiraProjectName = $validated['jira_project_name'];
        $arxitestProjectId = $validated['arxitest_project_id'];

        $arxitestProject = Project::findOrFail($arxitestProjectId);

        if ($arxitestProject->team_id !== session('current_team')) {
            Log::warning('User attempted import into project outside current team.', ['user_id' => Auth::id(), 'target_project_id' => $arxitestProjectId, 'current_team_id' => session('current_team')]);
            return back()->with('error', 'You do not have permission to import into this project.');
        }

        Log::info('Starting Jira project import.', ['jira_key' => $jiraProjectKey, 'arx_project' => $arxitestProject->name, 'user_id' => Auth::id()]);

        // --- Start Import Process (Consider Job for large imports) ---
        DB::beginTransaction();
        try {
            $jiraService = new JiraService($arxitestProject);
            $issues = $jiraService->getIssuesInProject(
                $jiraProjectKey,
                ['Epic', 'Story'],
                ['summary', 'description', 'issuetype', 'parent', 'status', 'created', 'updated', 'labels', 'priority', 'customfield_XXXXX'] // REPLACE customfield_XXXXX with your Epic Link field ID
            );

            $epicToSuiteMap = []; $storiesCreated = 0; $testCasesCreated = 0; $defaultSuite = null;

            // Process Epics -> TestSuites
            foreach ($issues as $issue) {
                 if (Arr::get($issue, 'fields.issuetype.name') === 'Epic') {
                     $epicName = Arr::get($issue, 'fields.summary', 'Untitled Epic ' . $issue['key']);
                     $suiteSettings = ['jira_epic_id' => $issue['id'], 'jira_epic_key' => $issue['key']];
                     $testSuite = TestSuite::updateOrCreate(
                         ['project_id' => $arxitestProject->id, 'settings->jira_epic_key' => $issue['key']],
                         ['name' => $epicName, 'description' => Arr::get($issue, 'fields.description', '') ?? 'From Epic: ' . $issue['key'], 'settings' => $suiteSettings]
                     );
                     $epicToSuiteMap[$issue['id']] = $testSuite->id;
                 }
            }

            // Process Stories -> Stories & TestCases
             foreach ($issues as $issue) {
                 if (Arr::get($issue, 'fields.issuetype.name') === 'Story') {
                    $storyTitle = Arr::get($issue, 'fields.summary', 'Untitled Story ' . $issue['key']);
                    $storyDescription = Arr::get($issue, 'fields.description', '') ?? '';
                    $parentEpicId = Arr::get($issue, 'fields.parent.id'); // Try parent field
                    $epicKeyFromCustomField = Arr::get($issue, 'fields.customfield_XXXXX'); // REPLACE XXXXX

                    $suiteId = null;
                    if ($parentEpicId && isset($epicToSuiteMap[$parentEpicId])) {
                        $suiteId = $epicToSuiteMap[$parentEpicId];
                    } elseif ($epicKeyFromCustomField) {
                        $matchingSuite = TestSuite::where('project_id', $arxitestProject->id)->where('settings->jira_epic_key', $epicKeyFromCustomField)->first();
                        if ($matchingSuite) $suiteId = $matchingSuite->id;
                    }
                    if (!$suiteId) { // Default suite
                        if (!$defaultSuite) $defaultSuite = TestSuite::firstOrCreate(['project_id' => $arxitestProject->id, 'name' => 'Imported (Uncategorized)'], ['description' => 'Imported Jira stories.', 'settings' => '{}']);
                        $suiteId = $defaultSuite->id;
                    }

                    $arxitestStory = ArxitestStory::updateOrCreate(
                        ['external_id' => $issue['key'], 'source' => 'jira'],
                        ['title' => $storyTitle, 'description' => $storyDescription, 'metadata' => json_encode(['jira_id' => $issue['id'], /* add other fields */])]
                    );
                    $storiesCreated++;

                    ArxitestTestCase::firstOrCreate(
                        ['story_id' => $arxitestStory->id, 'suite_id' => $suiteId, 'title' => 'Verify Story: ' . Str::limit($storyTitle, 200)],
                        ['steps' => json_encode([['action' => 'Verify: ' . $issue['key']]]), 'expected_results' => 'Feature works as described.']
                    );
                    $testCasesCreated++;
                 }
             }

            DB::commit();
            Log::info('Jira project import finished successfully.', ['jira_key' => $jiraProjectKey, 'arx_project' => $arxitestProject->name, 'stories' => $storiesCreated, 'cases' => $testCasesCreated, 'user_id' => Auth::id()]);
            return redirect()->route('dashboard.projects.show', $arxitestProject->id)->with('success', "Imported {$storiesCreated} stories and created {$testCasesCreated} test cases from '{$jiraProjectName}'.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during Jira project import: ' . $e->getMessage(), ['jira_key' => $jiraProjectKey, 'arx_project_id' => $arxitestProjectId, 'user_id' => Auth::id(), 'trace' => Str::limit($e->getTraceAsString(), 1500)]);
            return redirect()->route('integrations.jira.import.options', ['project_id' => $arxitestProjectId])->with('error', 'Import failed: ' . $e->getMessage());
        }
    }


    // --- GITHUB INTEGRATION (Placeholders) ---
    // ... githubRedirect and githubCallback would follow similar patterns ...

} // End of Controller Class
