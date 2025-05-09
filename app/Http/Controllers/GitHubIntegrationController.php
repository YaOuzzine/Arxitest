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
use Illuminate\Support\Str;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
            'max_file_size' => 'nullable|integer|min:1|max:128', // Maximum file size in KB (1-128KB)
            'auto_generate_tests' => 'nullable|boolean',
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

            // Create a unique job ID for tracking progress
            $jobId = Str::uuid()->toString();

            // Initialize progress at 0%
            cache()->put("github_project_progress_{$jobId}", [
                'progress' => 0,
                'status' => 'Initializing project creation',
                'team_id' => $team->id,
                'started_at' => now()->timestamp,
                'job_id' => $jobId
            ], 3600);

            // Create a job to handle this expensive operation
            $job = new \App\Jobs\CreateProjectFromGitHubRepo([
                'team_id' => $team->id,
                'integration_id' => $integration->id,
                'owner' => $request->input('owner'),
                'repo' => $request->input('repo'),
                'project_name' => $request->input('project_name'),
                'max_file_size' => $request->input('max_file_size', 64), // Default 64KB
                'auto_generate_tests' => true, // Always use AI generation with our improved method
                'user_id' => Auth::id(),
                'job_id' => $jobId
            ]);

            dispatch($job);

            return $this->successResponse([
                'job_id' => $jobId,
                'message' => 'Project creation has been queued. You will be notified when it completes.',
                'estimated_time' => '3-10 minutes'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating project from repo', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to create project: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get job progress information
     */
    public function getJobProgress(Request $request, string $jobId)
    {
        try {
            $progressData = cache()->get("github_project_progress_{$jobId}");

            if (!$progressData) {
                return $this->errorResponse('Job not found or has expired', 404);
            }

            // Check if the job belongs to this user's team
            $team = $this->getCurrentTeam($request);
            if ($progressData['team_id'] !== $team->id) {
                return $this->errorResponse('Unauthorized access to job data', 403);
            }

            // Add additional info for completed jobs
            if (isset($progressData['completed']) && $progressData['completed']) {
                // Calculate total duration
                $duration = $progressData['duration'] ?? 0;
                $progressData['duration_formatted'] = $this->formatDuration($duration);

                // Token stats summary if available
                if (isset($progressData['token_stats'])) {
                    $progressData['token_summary'] = $this->formatTokenSummary($progressData['token_stats']);
                }
            }

            return $this->successResponse($progressData);
        } catch (\Exception $e) {
            Log::error('Error getting job progress', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to get job progress: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format duration in seconds to human-readable string
     */
    protected function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} seconds";
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return "{$minutes} minutes, {$remainingSeconds} seconds";
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours} hours, {$remainingMinutes} minutes";
    }

    /**
     * Format token usage stats into a readable summary
     */
    protected function formatTokenSummary(array $stats): array
    {
        return [
            'files_processed' => $stats['files_included'] ?? 0,
            'files_skipped' => $stats['files_skipped'] ?? 0,
            'directories_scanned' => $stats['directories_included'] ?? 0,
            'tokens_used' => number_format($stats['estimated_tokens'] ?? 0),
            'characters_processed' => number_format($stats['total_chars'] ?? 0)
        ];
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

    /**
     * Save selected GitHub files to session for AI context
     */
    public function saveFilesToSession(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*.path' => 'required|string',
            'files.*.name' => 'required|string',
            'files.*.content' => 'required|string',
            'repo' => 'required|string',
            'owner' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Invalid file data format', 400);
        }

        // Limit to first 5 files to avoid making the context too large
        $files = array_slice($request->input('files'), 0, 5);

        // Store in session
        session([
            'github_context' => [
                'files' => $files,
                'repo' => $request->input('repo'),
                'owner' => $request->input('owner'),
                'added_at' => now()->toIso8601String()
            ]
        ]);

        return $this->successResponse([
            'files_count' => count($files)
        ], 'GitHub context saved to session');
    }

    /**
     * Remove a file from session context
     */
    public function removeFileFromContext(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filePath' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Invalid file path', 400);
        }

        if (!session()->has('github_context')) {
            return $this->successResponse([], 'No context to modify');
        }

        $context = session('github_context');
        $filePath = $request->input('filePath');

        // Filter out the file to be removed
        $context['files'] = array_filter($context['files'], function ($file) use ($filePath) {
            return $file['path'] !== $filePath;
        });

        // Re-index the array
        $context['files'] = array_values($context['files']);

        session(['github_context' => $context]);

        return $this->successResponse([
            'files_count' => count($context['files'])
        ], 'File removed from context');
    }

    /**
     * Clear all GitHub context from session
     */
    public function clearContext()
    {
        session()->forget('github_context');

        return $this->successResponse([], 'Context cleared successfully');
    }

    /**
     * Get contents of a folder recursively to support folder selection
     */
    public function getFolderContents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner' => 'required|string',
            'repo' => 'required|string',
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Invalid request', 400);
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
            $owner = $request->input('owner');
            $repo = $request->input('repo');
            $path = $request->input('path');

            $allFiles = $this->collectFilesRecursively($owner, $repo, $path);

            return $this->successResponse([
                'files' => $allFiles
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting folder contents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Failed to get folder contents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Recursively collect all files in a folder
     */
    private function collectFilesRecursively(string $owner, string $repo, string $path, int $maxFiles = 100): array
    {
        $allFiles = [];
        $queue = [[$path, 0]]; // [path, depth]
        $processedCount = 0;
        $maxDepth = 5; // Limit recursion depth

        while (!empty($queue) && $processedCount < $maxFiles) {
            [$currentPath, $depth] = array_shift($queue);

            // Skip if we've gone too deep
            if ($depth > $maxDepth) {
                continue;
            }

            try {
                $contents = $this->githubClient->getRepositoryContents($owner, $repo, $currentPath);

                // Skip if API returned an error or a file instead of directory contents
                if (!is_array($contents) || isset($contents['message']) || isset($contents['content'])) {
                    continue;
                }

                foreach ($contents as $item) {
                    $processedCount++;

                    if ($item['type'] === 'dir') {
                        // Add directory to queue for processing
                        $queue[] = [$item['path'], $depth + 1];
                    } else if ($item['type'] === 'file') {
                        // Skip very large files or binary files
                        if ($item['size'] > 100000 || $this->isBinaryFile($item['name'])) {
                            continue;
                        }

                        $allFiles[] = [
                            'path' => $item['path'],
                            'name' => $item['name'],
                            'type' => 'file',
                            'size' => $item['size'],
                            'url' => $item['html_url']
                        ];
                    }

                    // Stop if we've reached the limit
                    if ($processedCount >= $maxFiles) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Error processing path: {$currentPath}", [
                    'error' => $e->getMessage()
                ]);
                // Continue with other paths
            }
        }

        return $allFiles;
    }

    /**
 * Get GitHub files stored in session context
 */
public function getSessionContext()
{
    if (!session()->has('github_context')) {
        return $this->successResponse([
            'files' => [],
            'repo' => null,
            'owner' => null
        ]);
    }

    $context = session('github_context');

    return $this->successResponse([
        'files' => $context['files'] ?? [],
        'repo' => $context['repo'] ?? null,
        'owner' => $context['owner'] ?? null,
        'added_at' => $context['added_at'] ?? null
    ]);
}

    /**
     * Check if filename suggests a binary file
     */
    private function isBinaryFile(string $filename): bool
    {
        $binaryExtensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'bmp',
            'ico',
            'svg',
            'mp3',
            'mp4',
            'avi',
            'mov',
            'wmv',
            'flv',
            'zip',
            'rar',
            'tar',
            'gz',
            '7z',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'exe',
            'dll',
            'so',
            'dylib',
            'woff',
            'woff2',
            'ttf',
            'eot',
            'otf'
        ];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $binaryExtensions);
    }
}
