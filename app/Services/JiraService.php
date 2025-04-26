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
     * Fetch issues by JQL, with pagination.
     *
     * @param  string  $jql
     * @param  array   $fields
     * @param  int     $maxIssues
     * @return array
     * @throws Exception
     */
    public function getIssuesWithJql(string $jql, array $fields = [], int $maxIssues = 0): array
    {
        $this->ensureValidToken();

        $allIssues = [];
        $startAt   = 0;
        $pageSize  = min($maxIssues ?: 50, 100);

        do {
            $opts = [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"],
                'query'   => [
                    'jql'        => $jql,
                    'fields'     => implode(',', $fields),
                    'maxResults' => $pageSize,
                    'startAt'    => $startAt,
                ],
            ];

            $result     = $this->client->api($this->cloudId, 'get', '/rest/api/3/search', $opts);
            $issues     = $result['issues'] ?? [];
            $allIssues  = array_merge($allIssues, $issues);
            $fetched    = count($issues);
            $startAt   += $fetched;
        } while ($fetched > 0 && (!$maxIssues || $startAt < $maxIssues));

        return $allIssues;
    }

    /**
     * Fetch available issue types.
     *
     * @return array
     * @throws Exception
     */
    public function getIssueTypes(): array
    {
        $this->ensureValidToken();

        return $this->client
            ->api($this->cloudId, 'get', '/rest/api/3/issuetype', [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"]
            ]);
    }

    /**
     * Fetch available fields.
     *
     * @return array
     * @throws Exception
     */
    public function getFields(): array
    {
        $this->ensureValidToken();

        return $this->client
            ->api($this->cloudId, 'get', '/rest/api/3/field', [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"]
            ]);
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

        // Build JQL same as in getIssuesWithJql…
        $jqlParts = [];
        if (!empty($options['projectKey'])) {
            $jqlParts[] = sprintf('project = "%s"', str_replace('"','\"',$options['projectKey']));
        }
        if (!empty($options['issueTypes'])) {
            $escaped = implode('","', array_map(fn($t)=>str_replace('"','\"',$t), $options['issueTypes']));
            $jqlParts[] = "issuetype IN (\"{$escaped}\")";
        }
        if (!empty($options['statuses'])) {
            $escaped = implode('","', array_map(fn($s)=>str_replace('"','\"',$s), $options['statuses']));
            $jqlParts[] = "status IN (\"{$escaped}\")";
        }
        if (!empty($options['labels'])) {
            $labels = array_map(fn($l)=>sprintf('labels = "%s"',str_replace('"','\"',$l)), $options['labels']);
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
                'query'   => ['jql'=>$jql,'maxResults'=>0,'fields'=>'id']
            ]);
            return $response['total'] ?? 0;
        } catch (ApiException $e) {
            Log::error('Error counting Jira issues', ['jql'=>$jql,'error'=>$e->getMessage()]);
            return -1;
        }
    }
}
