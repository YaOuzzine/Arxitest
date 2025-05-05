<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\OAuthState;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Team;
use App\Services\GitHubApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Traits\JsonResponse;

class GitHubIntegrationController extends Controller
{
    use JsonResponse;

    protected GitHubApiClient $githubClient;

    public function __construct(GitHubApiClient $githubClient)
    {
        $this->githubClient = $githubClient;
    }

    /**
     * Redirect to GitHub authorization page
     */
    public function redirect(Request $request)
    {
        Log::debug('GitHub Integration configuration check', [
            'client_id_configured' => !empty(config('services.github_integration.client_id')),
            'client_id_preview' => !empty(config('services.github_integration.client_id')) ?
                substr(config('services.github_integration.client_id'), 0, 3) . '...' : 'not set',
            'redirect_uri' => config('services.github_integration.redirect'),
        ]);

        $userId = Auth::id();
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        // Store state for security validation
        $state = OAuthState::generateState($userId, $currentTeamId, 'github');

        // Define scopes needed for the integration
        $scopes = [
            'repo',           // Access repositories
            'read:user',      // Read user profile data
            'user:email',     // Access user email
        ];

        // Build the authorization URL
        $query = http_build_query([
            'client_id' => config('services.github_integration.client_id'),
            'redirect_uri' => config('services.github_integration.redirect'),
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'allow_signup' => false,
        ]);

        Log::info('Redirecting to GitHub OAuth', [
            'url' => "https://github.com/login/oauth/authorize?{$query}"
        ]);

        return redirect("https://github.com/login/oauth/authorize?{$query}");
    }

    /**
     * Handle the callback from GitHub authorization
     */
    public function callback(Request $request)
    {
        Log::info('GitHub integration callback received', [
            'has_code' => $request->has('code'),
            'code_preview' => $request->has('code') ? substr($request->code, 0, 5) . '...' : 'none',
            'has_state' => $request->has('state'),
            'state' => $request->state,
            'full_url' => $request->fullUrl(),
            'error' => $request->error,
            'error_description' => $request->error_description
        ]);

        $stateParam = $request->state;
        if (empty($stateParam)) {
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Invalid OAuth callback: missing state.');
        }

        $oauthState = OAuthState::where('state_token', $stateParam)
            ->where('expires_at', '>', now())
            ->first();

        if (!$oauthState) {
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'OAuth state verification failed.');
        }

        $userId = $oauthState->user_id;
        $teamId = $oauthState->project_id;
        $oauthState->delete();

        try {
            // Exchange the code for an access token
            $tokenData = $this->githubClient->exchangeCode($request->code);
            $accessToken = $tokenData['access_token'];

            // Set the token and fetch user details
            $this->githubClient->setAccessToken($accessToken);
            $userInfo = $this->githubClient->getUserDetails();

            // Create or update the integration record
            $integration = Integration::firstOrCreate(
                ['type' => Integration::TYPE_GITHUB],
                [
                    'name' => 'GitHub',
                    'base_url' => 'https://api.github.com',
                    'is_active' => true
                ]
            );

            // Find a project to store the integration with
            $team = Team::find($teamId);
            $project = $team->projects()->first() ?? Project::create([
                'name' => $team->name . ' – GitHub Credentials',
                'description' => 'Holds GitHub OAuth tokens for the team.',
                'team_id' => $team->id,
                'settings' => ['is_placeholder' => true],
            ]);

            // Store encrypted credentials
            $credentials = [
                'access_token' => $accessToken,
                'username' => $userInfo['login'],
                'user_id' => $userInfo['id'],
                'avatar_url' => $userInfo['avatar_url'],
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
                        'username' => $userInfo['login'],
                        'user_id' => $userInfo['id'],
                        'avatar_url' => $userInfo['avatar_url'],
                    ],
                ]
            );

            if (!Auth::check()) {
                Auth::loginUsingId($userId, true);
                session(['current_team' => $teamId]);
            }
            // Explicitly set the session value for GitHub connection status
            session(['github_connected' => true]);
            session()->save();

            // Log the session state for debugging
            Log::info('GitHub connection saved to session', [
                'github_connected' => session('github_connected'),
                'session_id' => session()->getId()
            ]);
            return redirect()->route('dashboard.integrations.index')
                ->with('success', 'GitHub connected successfully.');
        } catch (\Exception $e) {
            Log::error('GitHub OAuth error', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'GitHub authorization failed: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect GitHub integration
     */
    public function disconnect(Request $request)
    {
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'Team context is missing.');
        }

        $githubIntegration = Integration::where('type', Integration::TYPE_GITHUB)->first();
        if (!$githubIntegration) {
            return redirect()->route('dashboard.integrations.index')
                ->with('info', 'No GitHub integration config found.');
        }

        $deleted = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
            ->where('integration_id', $githubIntegration->id)
            ->delete();

        // Update session to reflect disconnected status
        session(['github_connected' => false]);
        session()->save();

        Log::info('GitHub integration disconnected', [
            'team_id' => $currentTeamId,
            'records_deleted' => $deleted,
            'session_updated' => true,
            'github_connected_session' => session('github_connected')
        ]);

        if ($deleted) {
            return redirect()->route('dashboard.integrations.index')
                ->with('success', 'GitHub disconnected successfully.')
                ->with('github_connected', false);  // Also add to flash data
        }

        return redirect()->route('dashboard.integrations.index')
            ->with('info', 'No active GitHub connections to disconnect.');
    }

    /**
     * List user repositories
     */
    public function listRepositories(Request $request)
    {
        try {
            $team = $this->getCurrentTeam($request);
            $integration = $this->getGitHubIntegration($team->id);

            if (!$integration) {
                return $this->errorResponse('GitHub integration not found.', 404);
            }

            $credentials = json_decode(Crypt::decryptString($integration->encrypted_credentials), true);
            $accessToken = $credentials['access_token'];

            $this->githubClient->setAccessToken($accessToken);
            $repositories = $this->githubClient->getRepositories();

            return $this->successResponse(['repositories' => $repositories]);
        } catch (\Exception $e) {
            Log::error('Error listing GitHub repositories', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to list repositories: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get repository details and contents
     */
    public function getRepository(Request $request, string $owner, string $repo)
    {
        try {
            $team = $this->getCurrentTeam($request);
            $integration = $this->getGitHubIntegration($team->id);

            if (!$integration) {
                return $this->errorResponse('GitHub integration not found.', 404);
            }

            $credentials = json_decode(Crypt::decryptString($integration->encrypted_credentials), true);
            $accessToken = $credentials['access_token'];

            $this->githubClient->setAccessToken($accessToken);

            $repository = $this->githubClient->getRepository($owner, $repo);
            $contents = $this->githubClient->getRepositoryContents($owner, $repo);

            return $this->successResponse([
                'repository' => $repository,
                'contents' => $contents
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting repository details', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to get repository: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get file or directory contents
     */
    public function getContents(Request $request, string $owner, string $repo, string $path = '')
    {
        try {
            $team = $this->getCurrentTeam($request);
            $integration = $this->getGitHubIntegration($team->id);

            if (!$integration) {
                return $this->errorResponse('GitHub integration not found.', 404);
            }

            $credentials = json_decode(Crypt::decryptString($integration->encrypted_credentials), true);
            $accessToken = $credentials['access_token'];

            $this->githubClient->setAccessToken($accessToken);

            // URL-decode the path
            $path = urldecode($path);
            $contents = $this->githubClient->getRepositoryContents($owner, $repo, $path);

            return $this->successResponse(['contents' => $contents]);
        } catch (\Exception $e) {
            Log::error('Error getting file contents', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to get contents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get file content
     */
    public function getFileContent(Request $request, string $owner, string $repo, string $path)
    {
        try {
            $team = $this->getCurrentTeam($request);
            $integration = $this->getGitHubIntegration($team->id);

            if (!$integration) {
                return $this->errorResponse('GitHub integration not found.', 404);
            }

            $credentials = json_decode(Crypt::decryptString($integration->encrypted_credentials), true);
            $accessToken = $credentials['access_token'];

            $this->githubClient->setAccessToken($accessToken);

            // URL-decode the path
            $path = urldecode($path);
            $content = $this->githubClient->getFileContent($owner, $repo, $path);

            return $this->successResponse(['content' => $content]);
        } catch (\Exception $e) {
            Log::error('Error getting file content', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to get file content: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a project from repository
     */
    public function createProjectFromRepo(Request $request)
    {
        $validator = validator($request->all(), [
            'owner' => 'required|string',
            'repo' => 'required|string',
            'project_name' => 'required|string|max:100',
            'max_files' => 'nullable|integer|min:1|max:100',
            'max_tokens' => 'nullable|integer|min:1000|max:50000',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $team = $this->getCurrentTeam($request);
            $integration = $this->getGitHubIntegration($team->id);

            if (!$integration) {
                return $this->errorResponse('GitHub integration not found.', 404);
            }

            $credentials = json_decode(Crypt::decryptString($integration->encrypted_credentials), true);
            $accessToken = $credentials['access_token'];

            $this->githubClient->setAccessToken($accessToken);

            // Create a job to handle this expensive operation
            $job = new \App\Jobs\CreateProjectFromGitHubRepo([
                'team_id' => $team->id,
                'integration_id' => $integration->id,
                'owner' => $request->input('owner'),
                'repo' => $request->input('repo'),
                'project_name' => $request->input('project_name'),
                'max_files' => $request->input('max_files', 20),
                'max_tokens' => $request->input('max_tokens', 10000),
                'user_id' => Auth::id(),
            ]);

            dispatch($job);

            return $this->successResponse([], 'Project creation has been queued. You will be notified when it completes.');
        } catch (\Exception $e) {
            Log::error('Error creating project from repo', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to create project: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get the GitHub integration for a team
     */
    protected function getGitHubIntegration(string $teamId): ?ProjectIntegration
    {
        return ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $teamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_GITHUB))
            ->where('is_active', true)
            ->first();
    }
}
