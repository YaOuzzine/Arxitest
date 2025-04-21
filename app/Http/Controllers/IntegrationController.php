<?php
// FILE: app/Http/Controllers/IntegrationController.php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\OAuthState;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    /**
     * Display the integrations management view.
     */
    public function index(Request $request)
    {
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Please select a team first.');
        }
        $team = Team::find($currentTeamId);
        if (!$team) {
            session()->forget('current_team'); // Clear invalid session
            return redirect()->route('dashboard.select-team')->with('error', 'Selected team not found.');
        }

        // Check if *any* project in the team has an active Jira connection
        $jiraConnected = ProjectIntegration::whereHas('project', fn ($q) => $q->where('team_id', $currentTeamId))
            ->whereHas('integration', fn ($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->exists();

        // Placeholder for GitHub check (similar logic)
        $githubConnected = ProjectIntegration::whereHas('project', fn ($q) => $q->where('team_id', $currentTeamId))
            ->whereHas('integration', fn ($q) => $q->where('type', Integration::TYPE_GITHUB))
            ->where('is_active', true)
            ->exists();

        // Pass a flag indicating if connection exists for enabling import buttons
        return view('dashboard.integrations.index', compact('jiraConnected', 'githubConnected', 'team'));
    }

    /**
     * Initiate Jira OAuth flow by redirecting to Atlassian.
     * IMPORTANT: This flow now stores the TEAM ID in the state, not a project ID.
     */
    public function jiraRedirect(Request $request)
    {
        $userId = Auth::id();
        $currentTeamId = session('current_team');

        if (!$userId || !$currentTeamId) {
            Log::error('Jira redirect attempted without authentication or team context', ['userId' => $userId, 'teamId' => $currentTeamId]);
            return redirect()->route('login')->with('error', 'Authentication or team selection required.');
        }

        // Generate state storing USER and TEAM context
        $state = OAuthState::generateState($userId, $currentTeamId); // Use team_id as the second param now

        Log::debug('Generating OAuth state for team', ['state' => $state, 'user_id' => $userId, 'team_id' => $currentTeamId]);

        // Build OAuth URL
        $query = http_build_query([
            'audience' => 'api.atlassian.com',
            'client_id' => config('services.atlassian.client_id'),
            'scope' => 'read:jira-user read:jira-work write:jira-work offline_access', // Ensure necessary scopes
            'redirect_uri' => route('integrations.jira.callback'), // Use named route for flexibility
            'state' => $state,
            'response_type' => 'code',
            'prompt' => 'consent',
        ]);

        Log::info('Redirecting to Atlassian for Jira OAuth (Team Level)', ['user_id' => $userId, 'team_id' => $currentTeamId]);
        return redirect('https://auth.atlassian.com/authorize?' . $query);
    }

    /**
     * Handle the callback from Atlassian after OAuth authorization.
     * Stores credentials globally for the integration first, then links to projects if needed.
     * Associates credentials with the TEAM via the first available project (simplification).
     */
    public function jiraCallback(Request $request)
    {
        Log::info('Jira OAuth callback received');

        $stateParam = $request->state;
        if (empty($stateParam)) {
            Log::error('Jira OAuth callback missing state parameter');
            return redirect()->route('login')->with('error', 'Invalid OAuth callback: Missing state.');
        }

        // Verify state, expects user_id and team_id (stored as project_id in OAuthState for reuse)
        $oauthState = OAuthState::where('state_token', $stateParam)
                                ->where('expires_at', '>', now())
                                ->first();

        if (!$oauthState) {
            Log::error('Jira OAuth invalid or expired state token', ['state' => $stateParam]);
            return redirect()->route('login')->with('error', 'OAuth verification failed: Invalid state token.');
        }

        $userId = $oauthState->user_id;
        $teamId = $oauthState->project_id; // We stored team_id here in redirect
        $oauthState->delete(); // State is used, delete it

        Log::debug('Valid OAuth state found', ['state_id' => $oauthState->id, 'user_id' => $userId, 'team_id' => $teamId]);

        // Ensure user and team exist
        $user = User::find($userId);
        $team = Team::find($teamId);
        if (!$user || !$team) {
            Log::error('User or Team not found during Jira callback', ['user_id' => $userId, 'team_id' => $teamId]);
            return redirect()->route('login')->with('error', 'User or Team context lost during authorization.');
        }

        // Log the user in if not already authenticated
        if (!Auth::check() || Auth::id() !== $userId) {
            Auth::login($user, true);
            $request->session()->regenerate();
            session(['current_team' => $teamId]); // Ensure team context is set
            Log::info('User re-authenticated in Jira callback', ['user_id' => $userId]);
        } elseif (!session('current_team')) {
            session(['current_team' => $teamId]); // Set team context if missing
            Log::info('Team context restored in Jira callback', ['team_id' => $teamId]);
        }

        // Handle potential errors from Atlassian
        if ($request->has('error')) {
            Log::error('Jira OAuth returned an error', ['error' => $request->error, 'description' => $request->error_description]);
            return redirect()->route('dashboard.integrations.index')->with('error', 'Jira authorization failed: ' . $request->input('error_description', $request->input('error', 'Unknown error')));
        }

        // Exchange code for tokens
        try {
            $tokenResponse = Http::asForm()->post('https://auth.atlassian.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.atlassian.client_id'),
                'client_secret' => config('services.atlassian.client_secret'),
                'code' => $request->code,
                'redirect_uri' => route('integrations.jira.callback'), // Use named route
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('Failed to get Jira access token', ['status' => $tokenResponse->status(), 'body' => $tokenResponse->body()]);
                throw new \Exception('Failed to retrieve access token (' . $tokenResponse->status() . ')');
            }
            $tokenData = $tokenResponse->json();

            // Get accessible resources (Jira sites)
            $resourceResponse = Http::withToken($tokenData['access_token'])
                ->get('https://api.atlassian.com/oauth/token/accessible-resources');

            if (!$resourceResponse->successful() || empty($resourceResponse->json())) {
                Log::error('Failed to get Jira resources', ['status' => $resourceResponse->status(), 'body' => $resourceResponse->body()]);
                throw new \Exception('Could not retrieve Jira sites or none found for your account.');
            }
            $resources = $resourceResponse->json();
            $jiraSite = $resources[0]; // Use the first accessible site

        } catch (\Exception $e) {
            Log::error('Error during Jira token/resource fetch', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard.integrations.index')->with('error', 'Error connecting to Jira: ' . $e->getMessage());
        }

        // Prepare credentials for storage
        $credentials = [
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds($tokenData['expires_in'] - 60)->timestamp, // Add buffer
            'cloud_id' => $jiraSite['id'],
            'site_url' => $jiraSite['url'],
            'site_name' => $jiraSite['name'],
            'scopes' => explode(' ', $tokenData['scope'] ?? '')
        ];

        // Store credentials securely
        try {
            $encryptedCredentials = Crypt::encryptString(json_encode($credentials));

             // Find or create the base Jira Integration record
             $integration = Integration::firstOrCreate(
                ['type' => Integration::TYPE_JIRA],
                ['name' => 'Jira', 'base_url' => 'https://api.atlassian.com', 'is_active' => true]
            );

            // **Simplification:** Associate credentials with the FIRST project of the team,
            // or create a dummy project if none exist. This project acts as the credential holder.
            // In a more robust system, credentials might be stored at the team level.
            $targetProject = $team->projects()->first();
            if (!$targetProject) {
                // If the team has no projects yet, create a placeholder one to hold the creds
                $targetProject = Project::create([
                    'name' => $team->name . ' - Jira Connection Holder',
                    'description' => 'Project created automatically to store Jira credentials for the team.',
                    'team_id' => $team->id,
                    'settings' => ['is_placeholder' => true] // Mark it
                ]);
                Log::info('Created placeholder project to store Jira credentials', ['project_id' => $targetProject->id, 'team_id' => $team->id]);
            }

            // Update or create the ProjectIntegration link for this project
            ProjectIntegration::updateOrCreate(
                [
                    'project_id' => $targetProject->id,
                    'integration_id' => $integration->id,
                ],
                [
                    'encrypted_credentials' => $encryptedCredentials,
                    'is_active' => true,
                    'project_specific_config' => [ // Store site info here
                        'cloud_id' => $jiraSite['id'],
                        'site_url' => $jiraSite['url'],
                        'site_name' => $jiraSite['name'],
                    ]
                ]
            );

            Log::info('Jira integration credentials stored successfully for team via project', [
                'team_id' => $team->id,
                'holding_project_id' => $targetProject->id,
                'jira_site' => $jiraSite['name']
            ]);

        } catch (DecryptException $e) {
            Log::error('Encryption error storing Jira credentials', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard.integrations.index')->with('error', 'Failed to securely store Jira connection (Encryption Error).');
        } catch (\Exception $e) {
            Log::error('Error storing Jira integration credentials', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard.integrations.index')->with('error', 'Failed to save Jira connection: ' . $e->getMessage());
        }

        // Success - redirect back to the integrations page
        return redirect()->route('dashboard.integrations.index')
            ->with('success', 'Jira connected successfully for team: ' . $team->name);
    }

    /**
     * Disconnect Jira integration for all projects in the current team.
     */
    public function jiraDisconnect(Request $request)
    {
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Team context lost.');
        }

        $jiraIntegration = Integration::where('type', Integration::TYPE_JIRA)->first();
        if (!$jiraIntegration) {
            return redirect()->route('dashboard.integrations.index')->with('info', 'Jira integration configuration not found.');
        }

        // Find all project integrations for this team and the Jira integration
        $deletedCount = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
            ->where('integration_id', $jiraIntegration->id)
            ->delete();

        if ($deletedCount > 0) {
            Log::info('Jira integrations disconnected for team', ['team_id' => $currentTeamId, 'count' => $deletedCount]);
            return redirect()->route('dashboard.integrations.index')->with('success', 'Jira integration disconnected from all projects in this team.');
        }

        return redirect()->route('dashboard.integrations.index')->with('info', 'No active Jira integrations found for this team to disconnect.');
    }
}
