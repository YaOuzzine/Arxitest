<?php

namespace App\Services;

use App\Models\Integration;
use App\Models\Project;
use App\Models\ProjectIntegration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JiraService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected ?ProjectIntegration $projectIntegration = null;
    protected ?array $credentials = null;
    protected ?string $accessToken = null;
    protected ?string $cloudId = null;
    protected ?string $baseUrl = null;

    /**
     * Constructor - Requires the Arxitest Project context.
     *
     * @param Project $project The Arxitest project linked to the Jira integration.
     * @throws \Exception If Jira integration is not configured for the project.
     */
    public function __construct(Project $project)
    {
        $this->clientId = config('services.atlassian.client_id');
        $this->clientSecret = config('services.atlassian.client_secret');
        $this->redirectUri = config('services.atlassian.redirect');

        Log::info("Initializing JiraService for project", [
            'project_id' => $project->id,
            'project_name' => $project->name
        ]);

        // Find the active Jira integration linked to this specific Arxitest project
        $this->projectIntegration = ProjectIntegration::where('project_id', $project->id)
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->first();

        if (!$this->projectIntegration) {
            Log::warning('No active Jira integration found for project', [
                'project_id' => $project->id
            ]);
            throw new \Exception("Jira integration is not configured for this project. Please connect Jira first.");
        }

        if (!$this->projectIntegration->encrypted_credentials) {
            Log::warning('Jira integration has no credentials', [
                'project_integration_id' => $this->projectIntegration->id
            ]);
            throw new \Exception("Jira integration credentials are missing. Please reconnect Jira.");
        }

        $this->loadCredentials();
    }

    /**
     * Load and decrypt credentials.
     * @throws \Exception
     */
    protected function loadCredentials(): void
    {
        try {
            $decrypted = Crypt::decryptString($this->projectIntegration->encrypted_credentials);
            $this->credentials = json_decode($decrypted, true);

            if (!is_array($this->credentials)) {
                Log::error('Decrypted credentials are not a valid array', [
                    'decrypted_content' => substr($decrypted, 0, 100) . '...',
                    'json_error' => json_last_error_msg(),
                    'project_integration_id' => $this->projectIntegration->id
                ]);
                throw new \Exception("Decrypted credentials are not a valid array.");
            }

            $this->accessToken = Arr::get($this->credentials, 'access_token');
            $this->cloudId = Arr::get($this->credentials, 'cloud_id');
            $siteUrl = rtrim(Arr::get($this->credentials, 'site_url', ''), '/');

            Log::debug('JiraService URL configured', [
                'site_url' => $siteUrl,
                'api_url' => $this->baseUrl,
                'cloud_id' => $this->cloudId
            ]);

            $this->baseUrl = "https://api.atlassian.com/ex/jira/{$this->cloudId}";


            // Additional detailed logging
            Log::debug('JiraService credentials loaded', [
                'has_access_token' => !empty($this->accessToken),
                'access_token_preview' => $this->accessToken ? (substr($this->accessToken, 0, 10) . '...') : 'none',
                'cloud_id' => $this->cloudId,
                'base_url' => $this->baseUrl,
                'expires_at' => Arr::get($this->credentials, 'expires_at'),
                'current_time' => time(),
                'token_expired' => (Arr::get($this->credentials, 'expires_at', 0) < time()) ? 'YES' : 'NO',
                'has_refresh_token' => !empty($this->credentials['refresh_token'])
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to decrypt or parse Jira credentials', [
                'project_integration_id' => $this->projectIntegration->id,
                'error' => $e->getMessage()
            ]);
            throw new \Exception("Could not load Jira credentials for this project: " . $e->getMessage());
        }

        if (!$this->accessToken || !$this->cloudId || !$this->baseUrl) {
            Log::error('Incomplete Jira credentials found', [
                'has_token' => !empty($this->accessToken),
                'has_cloud_id' => !empty($this->cloudId),
                'has_base_url' => !empty($this->baseUrl),
                'project_integration_id' => $this->projectIntegration->id
            ]);
            throw new \Exception("Incomplete Jira credentials found. Please reconnect Jira.");
        }

        // Fix API URL if needed
        if (Str::contains($this->baseUrl, '.atlassian.net')) {
            Log::debug("Using Jira Cloud site URL", [
                'base_url' => $this->baseUrl
            ]);
        } else {
            // If not an atlassian.net URL, use the API endpoint with cloud ID
            $originalUrl = $this->baseUrl;
            $this->baseUrl = "https://api.atlassian.com/ex/jira/{$this->cloudId}";
            Log::debug("Updated Jira API URL", [
                'original_url' => $originalUrl,
                'new_base_url' => $this->baseUrl
            ]);
        }
    }

    /**
     * Check if the access token is expired and refresh if necessary.
     *
     * @return bool True if token is valid or refreshed, false otherwise.
     */
    protected function ensureValidToken(): bool
    {
        $refreshToken = Arr::get($this->credentials, 'refresh_token');
        $expiresAt = Arr::get($this->credentials, 'expires_at', 0);

        Log::debug('Checking Jira token validity', [
            'expires_at' => $expiresAt,
            'current_time' => time(),
            'time_left_seconds' => $expiresAt - time(),
            'has_refresh_token' => !empty($refreshToken)
        ]);

        // Check if token expires within the next 5 minutes (300 seconds buffer)
        if (time() >= ($expiresAt - 300)) {
            Log::info('Jira access token expired or nearing expiry', [
                'project_integration_id' => $this->projectIntegration->id,
                'expires_at' => date('Y-m-d H:i:s', $expiresAt)
            ]);

            if (empty($refreshToken)) {
                Log::error('Jira access token expired, but no refresh token available', [
                    'project_integration_id' => $this->projectIntegration->id
                ]);
                // Deactivate the integration as we can't refresh
                $this->projectIntegration->update(['is_active' => false]);
                return false;
            }

            return $this->refreshToken($refreshToken);
        }

        return true; // Token is still valid
    }

    /**
     * Refresh the access token using the refresh token.
     *
     * @param string $refreshToken
     * @return bool True on success, false on failure.
     */
    protected function refreshToken(string $refreshToken): bool
    {
        Log::info('Attempting Jira token refresh', [
            'project_integration_id' => $this->projectIntegration->id
        ]);

        try {
            $response = Http::asForm()->post('https://auth.atlassian.com/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
            ]);

            Log::debug('Refresh token response received', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => Str::limit($response->body(), 200)
            ]);

            if (!$response->successful()) {
                Log::error('Failed to refresh Jira access token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'project_integration_id' => $this->projectIntegration->id
                ]);
                // Refresh token might be invalid, deactivate the integration
                $this->projectIntegration->update(['is_active' => false]);
                return false;
            }

            $newTokenData = $response->json();

            Log::info('Jira token refreshed successfully', [
                'project_integration_id' => $this->projectIntegration->id,
                'new_token_preview' => isset($newTokenData['access_token']) ?
                    substr($newTokenData['access_token'], 0, 10) . '...' : 'missing',
                'expires_in' => $newTokenData['expires_in'] ?? 'not set'
            ]);

            // Update credentials in memory
            $this->credentials['access_token'] = $newTokenData['access_token'];
            $this->credentials['expires_at'] = now()->addSeconds($newTokenData['expires_in'] - 60)->timestamp;

            // Atlassian might return a new refresh token
            if (isset($newTokenData['refresh_token'])) {
                $this->credentials['refresh_token'] = $newTokenData['refresh_token'];
                Log::debug('New refresh token received and stored');
            }

            $this->accessToken = $this->credentials['access_token'];

            // Save updated credentials back to the database
            try {
                $this->projectIntegration->update([
                    'encrypted_credentials' => Crypt::encryptString(json_encode($this->credentials))
                ]);
                Log::debug('Updated Jira credentials saved to database', [
                    'project_integration_id' => $this->projectIntegration->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to save refreshed Jira credentials', [
                    'project_integration_id' => $this->projectIntegration->id,
                    'error' => $e->getMessage()
                ]);
                // Continue using the in-memory token for this request
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Exception during token refresh', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Make an authenticated API request to Jira.
     * Handles token refresh automatically.
     *
     * @param string $method HTTP method (get, post, put, delete)
     * @param string $endpoint API endpoint (e.g., '/rest/api/3/project')
     * @param array $options Request options (query params, body, etc.)
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception If the request fails after potential refresh.
     */
    protected function makeRequest(string $method, string $endpoint, array $options = [])
    {
        if (!$this->ensureValidToken()) {
            throw new \Exception("Jira token is invalid and could not be refreshed. Please reconnect Jira.");
        }

        // Ensure endpoint starts with a slash
        if (!Str::startsWith($endpoint, '/')) {
            $endpoint = '/' . $endpoint;
        }

        // Construct the full URL
        $url = $this->baseUrl . $endpoint;

        Log::debug("Making Jira API Request", [
            'method' => strtoupper($method),
            'url' => $url,
            'options' => $method === 'get' ? $options : [],
            'authorization' => 'Bearer ' . substr($this->accessToken, 0, 5) . '...',
            'project_integration_id' => $this->projectIntegration->id
        ]);

        // First attempt using standard Http::withToken
        $response = Http::withToken($this->accessToken)
            ->withHeaders(['Accept' => 'application/json'])
            ->{$method}($url, $options);

        // If that fails with 401, try with explicit Authorization header
        if ($response->status() === 401) {
            Log::warning('Jira API returned 401 with standard auth. Trying explicit header', [
                'endpoint' => $endpoint
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json'
            ])->{$method}($url, $options);
        }

        // Log response details
        Log::debug('Jira API Response', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body_preview' => Str::limit($response->body(), 200)
        ]);

        // Check if token expired during request (401 Unauthorized)
        if ($response->status() === 401) {
            Log::warning('Jira API returned 401 Unauthorized. Attempting token refresh', [
                'endpoint' => $endpoint,
                'project_integration_id' => $this->projectIntegration->id
            ]);

            // Attempt refresh again ONLY if we have a refresh token
            if (!empty($this->credentials['refresh_token']) && $this->refreshToken($this->credentials['refresh_token'])) {
                // Retry the request with the new token
                Log::info('Retrying Jira API request after successful token refresh', [
                    'endpoint' => $endpoint
                ]);

                $response = Http::withToken($this->accessToken)
                    ->acceptJson()
                    ->{$method}($url, $options);

                // Log retry response
                Log::debug('Jira API Retry Response', [
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);
            } else {
                Log::error('Jira token refresh failed or not possible after 401', [
                    'project_integration_id' => $this->projectIntegration->id
                ]);

                // Deactivate if refresh failed
                if (empty($this->credentials['refresh_token'])) {
                    $this->projectIntegration->update(['is_active' => false]);
                }
                throw new \Exception("Jira token became invalid and could not be refreshed. Please reconnect Jira.");
            }
        }

        if (!$response->successful()) {
            Log::error('Jira API request failed', [
                'status' => $response->status(),
                'endpoint' => $endpoint,
                'url' => $url,
                'response_body' => $response->body(),
                'project_integration_id' => $this->projectIntegration->id
            ]);

            $errorBody = $response->json();
            // Jira often puts errors in 'errorMessages' or 'errors'
            $errorMessages = Arr::get($errorBody, 'errorMessages', []);
            $errors = Arr::get($errorBody, 'errors', []);

            if (!empty($errorMessages) && is_array($errorMessages)) {
                $errorMessage = implode(', ', $errorMessages);
            } elseif (!empty($errors) && is_array($errors)) {
                $errorMessage = implode(', ', array_map(
                    fn($k, $v) => "$k: $v",
                    array_keys($errors),
                    array_values($errors)
                ));
            } else {
                $errorMessage = 'An unknown error occurred with the Jira API.';
            }

            throw new \Exception("Jira API Error ({$response->status()}): " . $errorMessage);
        }

        Log::debug("Jira API Response Successful", [
            'status' => $response->status(),
            'endpoint' => $endpoint
        ]);

        return $response;
    }

    /**
     * Get projects accessible by the user.
     *
     * @return array List of projects [[id, key, name, avatarUrls], ...]
     * @throws \Exception
     */
    public function getProjects(): array
    {
        try {
            Log::info('Fetching Jira projects');

            $response = $this->makeRequest('get', '/rest/api/3/project/search', [
                'maxResults' => 100,
                'orderBy' => 'name',
                'expand' => '' // No expansions needed for now
            ]);

            $results = $response->json();
            $projects = $results['values'] ?? [];

            Log::info('Jira projects fetched successfully', [
                'count' => count($projects),
                'total' => $results['total'] ?? 'unknown'
            ]);

            return $projects;
        } catch (\Exception $e) {
            Log::error('Error fetching Jira projects: ' . $e->getMessage());
            throw $e; // Rethrow to notify caller
        }
    }

    /**
     * Get issues (Epics, Stories) from a specific project.
     * Handles pagination.
     *
     * @param string $projectKey The Jira project key (e.g., "ARX").
     * @param array $issueTypes Array of issue types to fetch (e.g., ['Epic', 'Story']).
     * @param array $fields Array of fields to retrieve for each issue.
     * @return array List of issues.
     * @throws \Exception
     */
    public function getIssuesInProject(string $projectKey, array $issueTypes = ['Epic', 'Story'], array $fields = ['summary', 'description', 'issuetype', 'parent', 'status', 'created', 'updated', 'priority', 'labels']): array
    {
        $allIssues = [];
        $startAt = 0;
        $maxResults = 50; // Jira's common page size

        // Sanitize issue types for JQL query
        $sanitizedIssueTypes = array_map(function($type) {
            // Basic sanitization: remove quotes and escape existing quotes
            return str_replace('"', '\"', trim($type, '"'));
        }, $issueTypes);
        $issueTypeString = '"' . implode('", "', $sanitizedIssueTypes) . '"';

        // Construct JQL query safely
        $escapedProjectKey = str_replace('"', '\"', $projectKey);
        $jql = sprintf('project = "%s" AND issueType IN (%s) ORDER BY created DESC', $escapedProjectKey, $issueTypeString);

        Log::info('Fetching issues from Jira project', [
            'projectKey' => $projectKey,
            'issueTypes' => implode(', ', $issueTypes),
            'jql' => $jql
        ]);

        $pagesRetrieved = 0;
        $total = 0;

        do {
            try {
                $response = $this->makeRequest('get', '/rest/api/3/search', [
                    'jql' => $jql,
                    'fields' => implode(',', $fields),
                    'maxResults' => $maxResults,
                    'startAt' => $startAt,
                ]);

                $data = $response->json();
                $issues = $data['issues'] ?? [];
                $allIssues = array_merge($allIssues, $issues);

                $total = $data['total'] ?? 0;
                $countCurrentPage = count($issues);
                $startAt += $countCurrentPage;
                $pagesRetrieved++;

                Log::debug('Fetched page of Jira issues', [
                    'projectKey' => $projectKey,
                    'page' => $pagesRetrieved,
                    'startAt' => $startAt - $countCurrentPage,
                    'count' => $countCurrentPage,
                    'total' => $total
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to fetch a page of Jira issues', [
                    'projectKey' => $projectKey,
                    'startAt' => $startAt,
                    'error' => $e->getMessage()
                ]);
                throw $e; // Re-throw for now
            }

        // Ensure we don't loop infinitely if the API returns unexpected data
        } while ($startAt < $total && $countCurrentPage > 0 && $pagesRetrieved < 10); // Added max pages safety limit

        Log::info('Finished fetching Jira issues', [
            'projectKey' => $projectKey,
            'total_fetched' => count($allIssues),
            'pages' => $pagesRetrieved
        ]);

        return $allIssues;
    }
}
