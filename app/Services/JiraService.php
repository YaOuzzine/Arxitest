<?php

namespace App\Services;

use App\Models\Integration;
use App\Models\Project;
use App\Models\ProjectIntegration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JiraService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected ?ProjectIntegration $projectIntegration = null;
    protected ?array $credentials = null;
    protected ?string $accessToken = null;
    protected ?string $cloudId = null;
    protected ?string $baseUrl = null; // Base URL for API calls (e.g., https://your-site.atlassian.net)

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

        // Find the active Jira integration linked to this specific Arxitest project
        $this->projectIntegration = ProjectIntegration::where('project_id', $project->id)
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->first();

        if (!$this->projectIntegration || !$this->projectIntegration->encrypted_credentials) {
            Log::warning('JiraService: No active Jira integration found for project.', ['project_id' => $project->id]);
            throw new \Exception("Jira integration is not configured or active for this project.");
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
                 throw new \Exception("Decrypted credentials are not a valid array.");
            }

            $this->accessToken = Arr::get($this->credentials, 'access_token');
            $this->cloudId = Arr::get($this->credentials, 'cloud_id');
            $this->baseUrl = rtrim(Arr::get($this->credentials, 'site_url', ''), '/'); // Use site_url from credentials

        } catch (\Exception $e) {
            Log::error('Failed to decrypt or parse Jira credentials', [
                'project_integration_id' => $this->projectIntegration->id,
                'error' => $e->getMessage()
            ]);
            throw new \Exception("Could not load Jira credentials for this project. They might be corrupted.");
        }

        if (!$this->accessToken || !$this->cloudId || !$this->baseUrl) {
            Log::error('Incomplete Jira credentials found.', [
                'project_integration_id' => $this->projectIntegration->id,
                'has_token' => !empty($this->accessToken),
                'has_cloud_id' => !empty($this->cloudId),
                'has_base_url' => !empty($this->baseUrl),
            ]);
            throw new \Exception("Incomplete Jira credentials found for this project. Please reconnect.");
        }
        Log::debug('Jira Credentials loaded successfully.', ['project_integration_id' => $this->projectIntegration->id, 'baseUrl' => $this->baseUrl]);
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

        // Check if token expires within the next 5 minutes (300 seconds buffer)
        if (time() >= ($expiresAt - 300)) {
            Log::info('Jira access token expired or nearing expiry.', ['project_integration_id' => $this->projectIntegration->id]);

            if (empty($refreshToken)) {
                Log::error('Jira access token expired, but no refresh token available.', ['project_integration_id' => $this->projectIntegration->id]);
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
         Log::info('Attempting Jira token refresh.', ['project_integration_id' => $this->projectIntegration->id]);

        $response = Http::asForm()->post('https://auth.atlassian.com/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        if (!$response->successful()) {
            Log::error('Failed to refresh Jira access token.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'project_integration_id' => $this->projectIntegration->id
            ]);
            // Refresh token might be invalid, deactivate the integration
            $this->projectIntegration->update(['is_active' => false]);
            return false;
        }

        $newTokenData = $response->json();
        Log::info('Jira token refreshed successfully.', ['project_integration_id' => $this->projectIntegration->id]);

        // Update credentials in memory
        $this->credentials['access_token'] = $newTokenData['access_token'];
        $this->credentials['expires_at'] = now()->addSeconds($newTokenData['expires_in'] - 60)->timestamp;
        // Atlassian *might* return a new refresh token, but usually doesn't for standard OAuth 2.0 flows.
        // If they do, uncomment the line below. Check their docs for certainty.
        // $this->credentials['refresh_token'] = $newTokenData['refresh_token'] ?? $refreshToken;
        $this->accessToken = $this->credentials['access_token'];

        // Save updated credentials back to the database
        try {
            $this->projectIntegration->update([
                'encrypted_credentials' => Crypt::encryptString(json_encode($this->credentials))
            ]);
            Log::debug('Updated Jira credentials in database after refresh.', ['project_integration_id' => $this->projectIntegration->id]);
        } catch (\Exception $e) {
             Log::error('Failed to save refreshed Jira credentials', [
                'project_integration_id' => $this->projectIntegration->id,
                'error' => $e->getMessage()
            ]);
             // Continue using the in-memory token for this request, but log the persistence error.
        }

        return true;
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
            throw new \Exception("Jira token is invalid and could not be refreshed. Please reconnect.");
        }

        // Construct the full URL using the site's base URL
        $url = $this->baseUrl . $endpoint;

        Log::debug("Making Jira API Request", [
            'method' => $method,
            'url' => $url,
            'options' => $method === 'get' ? $options : [], // Log query params for GET
            'project_integration_id' => $this->projectIntegration->id
        ]);

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->{$method}($url, $options);

        // Check if token expired during request (401 Unauthorized)
        if ($response->status() === 401) {
            Log::warning('Jira API returned 401 Unauthorized. Attempting token refresh.', ['endpoint' => $endpoint, 'project_integration_id' => $this->projectIntegration->id]);
             // Attempt refresh again ONLY if we have a refresh token
            if (!empty($this->credentials['refresh_token']) && $this->refreshToken($this->credentials['refresh_token'])) {
                // Retry the request with the new token
                Log::info('Retrying Jira API request after successful token refresh.', ['endpoint' => $endpoint]);
                $response = Http::withToken($this->accessToken)
                    ->acceptJson()
                    ->{$method}($url, $options);
            } else {
                Log->error('Jira token refresh failed or not possible after 401.', ['project_integration_id' => $this->projectIntegration->id]);
                // Deactivate if refresh failed
                 if (empty($this->credentials['refresh_token'])) {
                     $this->projectIntegration->update(['is_active' => false]);
                 }
                 throw new \Exception("Jira token became invalid and could not be refreshed. Please reconnect the integration.");
            }
        }

         if (!$response->successful()) {
            Log::error('Jira API request failed.', [
                'status' => $response->status(),
                'endpoint' => $endpoint,
                'url' => $url,
                'response_body' => $response->body(),
                'project_integration_id' => $this->projectIntegration->id
            ]);

            $errorBody = $response->json();
            // Jira often puts errors in 'errorMessages' or 'errors'
            $errorMessage = Arr::get($errorBody, 'errorMessages.0');
            if (!$errorMessage && is_array(Arr::get($errorBody, 'errors'))) {
                 // Sometimes errors are key-value pairs
                 $errorMessage = implode(', ', array_map(
                     fn($k, $v) => "$k: $v",
                     array_keys(Arr::get($errorBody, 'errors')),
                     Arr::get($errorBody, 'errors')
                 ));
            }
             if (!$errorMessage) {
                 $errorMessage = 'An unknown error occurred with the Jira API.';
             }

            throw new \Exception("Jira API Error ({$response->status()}): " . $errorMessage);
         }


        Log::debug("Jira API Response Received Successfully", ['status' => $response->status(), 'endpoint' => $endpoint]);
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
        $response = $this->makeRequest('get', '/rest/api/3/project/search', [
            'maxResults' => 100, // Adjust pagination if needed for > 100 projects
            'orderBy' => 'name',
            'expand' => 'avatarUrls' // Request avatar URLs
        ]);

        return $response->json()['values'] ?? [];
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

        Log::info('Fetching issues from Jira project.', ['projectKey' => $projectKey, 'jql' => $jql]);

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

                Log::debug('Fetched page of Jira issues.', [
                    'projectKey' => $projectKey,
                    'startAt' => $startAt - $countCurrentPage,
                    'count' => $countCurrentPage,
                    'total' => $total
                ]);

            } catch (\Exception $e) {
                 Log::error('Failed to fetch a page of Jira issues.', ['projectKey' => $projectKey, 'startAt' => $startAt, 'error' => $e->getMessage()]);
                 // Depending on desired behavior, you might re-throw, or return partial results
                 throw $e; // Re-throw for now
            }

        // Ensure we don't loop infinitely if the API returns unexpected data
        } while ($startAt < $total && $countCurrentPage > 0 && $countCurrentPage === $maxResults);

        Log::info('Finished fetching issues from Jira project.', ['projectKey' => $projectKey, 'total_fetched' => count($allIssues)]);
        return $allIssues;
    }
}
