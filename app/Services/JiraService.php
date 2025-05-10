<?php

namespace App\Services;

use App\Models\Integration;
use App\Models\Project;
use App\Models\ProjectIntegration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Exception;

class JiraService
{
    protected JiraApiClient       $client;
    protected ProjectIntegration  $integration;
    protected string              $accessToken;
    protected string              $refreshToken;
    protected int                 $expiresAt;
    protected string              $cloudId;

    /**
     * Constructor - accept Project or team ID
     *
     * @param  Project|string  $contextOrId
     * @throws Exception
     */
    public function __construct($contextOrId)
    {
        $this->client = app(JiraApiClient::class);

        if ($contextOrId instanceof Project) {
            $this->initFromProject($contextOrId);
        } else {
            $this->initFromTeamId($contextOrId);
        }

        $this->loadCredentials();
    }

    private function initFromProject(Project $project): void
    {
        Log::info('Initializing JiraService from project', ['project_id' => $project->id]);

        $this->integration = ProjectIntegration::where('project_id', $project->id)
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->first();

        if (! $this->integration) {
            throw new Exception('Jira integration not configured for this project.');
        }
    }

    private function initFromTeamId(string $teamId): void
    {
        Log::info('Initializing JiraService from team ID', ['team_id' => $teamId]);

        $this->integration = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $teamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->first();

        if (! $this->integration) {
            throw new Exception('No active Jira integration found for this team.');
        }
    }

    /**
     * Decrypt & load tokens.
     *
     * @throws Exception
     */
    protected function loadCredentials(): void
    {
        try {
            $decrypted = Crypt::decryptString($this->integration->encrypted_credentials);
            $creds     = json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);

            $this->accessToken  = Arr::get($creds, 'access_token');
            $this->refreshToken = Arr::get($creds, 'refresh_token');
            $this->expiresAt    = Arr::get($creds, 'expires_at', 0);
            $this->cloudId      = Arr::get($creds, 'cloud_id');

            if (! $this->accessToken || ! $this->cloudId) {
                throw new Exception('Incomplete Jira credentials.');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to load Jira credentials', ['error' => $e->getMessage()]);
            throw new Exception('Could not load Jira credentials: ' . $e->getMessage());
        }
    }

    /**
     * Ensure token is valid, refresh if near expiry.
     *
     * @throws Exception
     */
    protected function ensureValidToken(): void
    {
        if (time() >= ($this->expiresAt - 300)) {
            Log::info('Refreshing Jira access token', ['integration_id' => $this->integration->id]);

            try {
                $response = $this->client->refreshToken($this->refreshToken);

                $this->accessToken  = Arr::get($response, 'access_token');
                $this->refreshToken = Arr::get($response, 'refresh_token', $this->refreshToken);
                $this->expiresAt    = now()->addSeconds(Arr::get($response, 'expires_in', 3600))->timestamp;

                // Persist new credentials
                $newCreds = [
                    'access_token'  => $this->accessToken,
                    'refresh_token' => $this->refreshToken,
                    'expires_at'    => $this->expiresAt,
                    'cloud_id'      => $this->cloudId,
                ];

                $this->integration->update([
                    'encrypted_credentials' => Crypt::encryptString(json_encode($newCreds)),
                ]);
            } catch (ApiException $e) {
                Log::error('Jira token refresh failed', ['error' => $e->getMessage()]);
                $this->integration->update(['is_active' => false]);
                throw new Exception('Jira token refresh failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Fetch list of Jira projects.
     *
     * @return array
     * @throws Exception
     */
    public function getProjects(): array
    {
        $this->ensureValidToken();

        $response = $this->client->api(
            $this->cloudId,
            'get',
            '/rest/api/3/project/search',
            [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"],
                'query'   => ['maxResults' => 100, 'orderBy' => 'name'],
            ]
        );

        return $response['values'] ?? [];
    }

    /**
     * Get detailed project information including statistics
     *
     * @param string $projectKey
     * @return array
     * @throws Exception
     */
    public function getProjectDetails(string $projectKey): array
    {
        $this->ensureValidToken();

        // Get project info
        $projectInfo = $this->client->api(
            $this->cloudId,
            'get',
            "/rest/api/3/project/{$projectKey}",
            [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"]
            ]
        );

        // Get issue types
        $issueTypes = $this->client->api(
            $this->cloudId,
            'get',
            "/rest/api/3/issuetype",
            [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"]
            ]
        );

        // Get project stats
        $counts = [
            'epics' => $this->getFilteredIssuesCount([
                'projectKey' => $projectKey,
                'issueTypes' => ['Epic']
            ]),
            'stories' => $this->getFilteredIssuesCount([
                'projectKey' => $projectKey,
                'issueTypes' => ['Story']
            ]),
            'bugs' => $this->getFilteredIssuesCount([
                'projectKey' => $projectKey,
                'issueTypes' => ['Bug']
            ]),
            'tasks' => $this->getFilteredIssuesCount([
                'projectKey' => $projectKey,
                'issueTypes' => ['Task']
            ])
        ];

        return [
            'info' => $projectInfo,
            'issueTypes' => $issueTypes,
            'counts' => $counts
        ];
    }

    /**
     * Fetch issues by JQL with better pagination and logging.
     *
     * @param string $jql JQL query
     * @param array $fields Fields to return
     * @param int $maxIssues Maximum issues to return (0 for unlimited)
     * @return array
     * @throws \Exception
     */
    public function getIssuesWithJql(string $jql, array $fields = [], int $maxIssues = 0): array
    {
        $this->ensureValidToken();

        $allIssues = [];
        $startAt = 0;
        $pageSize = 100; // Max per page
        $totalFetched = 0;
        $totalAvailable = null;

        Log::info('Starting JQL query', [
            'jql' => $jql,
            'fields_count' => count($fields),
            'max_issues' => $maxIssues
        ]);

        do {
            try {
                $opts = [
                    'headers' => ['Authorization' => "Bearer {$this->accessToken}"],
                    'query' => [
                        'jql' => $jql,
                        'fields' => implode(',', $fields),
                        'maxResults' => $pageSize,
                        'startAt' => $startAt,
                    ],
                ];

                Log::debug('Fetching page of issues', [
                    'start_at' => $startAt,
                    'page_size' => $pageSize,
                    'total_fetched_so_far' => $totalFetched
                ]);

                $result = $this->client->api($this->cloudId, 'get', '/rest/api/3/search', $opts);

                $issues = $result['issues'] ?? [];
                $totalAvailable = $result['total'] ?? 0;
                $fetched = count($issues);

                Log::debug('Issues page received', [
                    'fetched_in_page' => $fetched,
                    'total_available' => $totalAvailable
                ]);

                $allIssues = array_merge($allIssues, $issues);
                $totalFetched += $fetched;
                $startAt += $fetched;

                // Prevent rate limiting
                if ($fetched > 0 && $startAt < $totalAvailable) {
                    usleep(100000); // 100ms pause between requests
                }
            } catch (\Exception $e) {
                Log::error('Error fetching Jira issues', [
                    'error' => $e->getMessage(),
                    'jql' => $jql
                ]);
                throw new \Exception('Failed to fetch issues: ' . $e->getMessage());
            }
        } while (
            $fetched > 0 &&
            $startAt < $totalAvailable &&
            ($maxIssues === 0 || $totalFetched < $maxIssues)
        );

        Log::info('JQL query completed', [
            'total_fetched' => $totalFetched,
            'total_available' => $totalAvailable
        ]);

        return $allIssues;
    }

    /**
     * Get issues by type with optional filters
     *
     * @param string $projectKey
     * @param string $issueType
     * @param array $filters Additional JQL filters
     * @return array
     */
    public function getIssuesByType(string $projectKey, string $issueType, array $filters = []): array
    {
        $jqlParts = [
            "project = \"{$projectKey}\"",
            "issuetype = \"{$issueType}\""
        ];

        foreach ($filters as $key => $value) {
            if ($key === 'statuses' && is_array($value)) {
                $escaped = implode('","', array_map(fn($s) => str_replace('"', '\"', $s), $value));
                $jqlParts[] = "status IN (\"{$escaped}\")";
            } else if ($key === 'labels' && is_array($value)) {
                $labels = array_map(fn($l) => sprintf('labels = "%s"', str_replace('"', '\"', $l)), $value);
                $jqlParts[] = '(' . implode(' OR ', $labels) . ')';
            } else if ($key === 'customJql' && !empty($value)) {
                $jqlParts[] = "({$value})";
            }
        }

        $jql = implode(' AND ', $jqlParts) . ' ORDER BY created DESC';
        $fields = [
            'summary',
            'description',
            'issuetype',
            'parent',
            'status',
            'created',
            'updated',
            'labels',
            'priority',
            'assignee',
            'components'
        ];

        return $this->getIssuesWithJql($jql, $fields);
    }

    /**
     * Fetch count of issues matching custom filters.
     *
     * @param  array  $options
     * @return int
     */
    public function getFilteredIssuesCount(array $options = []): int
    {
        $this->ensureValidToken();

        // Build JQL
        $jqlParts = [];
        if (!empty($options['projectKey'])) {
            $jqlParts[] = sprintf('project = "%s"', str_replace('"', '\"', $options['projectKey']));
        }
        if (!empty($options['issueTypes'])) {
            $escaped = implode('","', array_map(fn($t) => str_replace('"', '\"', $t), $options['issueTypes']));
            $jqlParts[] = "issuetype IN (\"{$escaped}\")";
        }
        if (!empty($options['statuses'])) {
            $escaped = implode('","', array_map(fn($s) => str_replace('"', '\"', $s), $options['statuses']));
            $jqlParts[] = "status IN (\"{$escaped}\")";
        }
        if (!empty($options['labels'])) {
            $labels = array_map(fn($l) => sprintf('labels = "%s"', str_replace('"', '\"', $l)), $options['labels']);
            $jqlParts[] = '(' . implode(' OR ', $labels) . ')';
        }
        if (!empty($options['customJql'])) {
            $jqlParts[] = '(' . $options['customJql'] . ')';
        }

        if (empty($jqlParts)) {
            throw new \InvalidArgumentException('Cannot count issues without any filter.');
        }

        $jql = implode(' AND ', $jqlParts);

        try {
            $response = $this->client->api($this->cloudId, 'get', '/rest/api/3/search', [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"],
                'query'   => ['jql' => $jql, 'maxResults' => 0, 'fields' => 'id']
            ]);
            return $response['total'] ?? 0;
        } catch (ApiException $e) {
            Log::error('Error counting Jira issues', ['jql' => $jql, 'error' => $e->getMessage()]);
            return -1;
        }
    }

    /**
     * Find an issue by key
     *
     * @param string $issueKey
     * @return array|null
     */
    public function getIssueByKey(string $issueKey): ?array
    {
        $this->ensureValidToken();

        try {
            return $this->client->api(
                $this->cloudId,
                'get',
                "/rest/api/3/issue/{$issueKey}",
                [
                    'headers' => ['Authorization' => "Bearer {$this->accessToken}"]
                ]
            );
        } catch (ApiException $e) {
            Log::error('Error fetching Jira issue', ['key' => $issueKey, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
