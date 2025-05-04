<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GitHubApiClient extends ApiClient
{
    protected array $config;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->config = config('services.github', []);
        parent::__construct();
    }

    protected function config(string $key): mixed
    {
        if (!array_key_exists($key, $this->config)) {
            throw new \InvalidArgumentException("GitHub config key [$key] is not defined.");
        }
        return $this->config[$key];
    }

    protected function providerName(): string
    {
        return 'GitHub';
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCode(string $code): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post('https://github.com/login/oauth/access_token', [
            'client_id' => $this->config('client_id'),
            'client_secret' => $this->config('client_secret'),
            'code' => $code,
            'redirect_uri' => $this->config('redirect_uri'),
        ]);

        if ($response->failed()) {
            Log::error('GitHub token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new ApiException('Failed to exchange code for token', $response->status());
        }

        return $response->json();
    }

    /**
     * Get authenticated user details
     */
    public function getUserDetails(): array
    {
        return $this->get('user');
    }

    /**
     * Get user repositories
     */
    public function getRepositories(array $params = []): array
    {
        $defaultParams = [
            'sort' => 'updated',
            'per_page' => 100
        ];

        $queryParams = array_merge($defaultParams, $params);
        return $this->get('user/repos', $queryParams);
    }

    /**
     * Get repository details
     */
    public function getRepository(string $owner, string $repo): array
    {
        return $this->get("repos/{$owner}/{$repo}");
    }

    /**
     * Get repository contents
     */
    public function getRepositoryContents(string $owner, string $repo, string $path = ''): array
    {
        return $this->get("repos/{$owner}/{$repo}/contents/{$path}");
    }

    /**
     * Get file content
     */
    public function getFileContent(string $owner, string $repo, string $path): string
    {
        $response = $this->get("repos/{$owner}/{$repo}/contents/{$path}");

        if (isset($response['content'])) {
            return base64_decode($response['content']);
        }

        throw new \Exception("Could not get file content for {$path}");
    }

    /**
     * Helper method for GET requests
     */
    protected function get(string $endpoint, array $params = []): array
    {
        if (!$this->accessToken) {
            throw new \Exception('Access token is required. Please authenticate first.');
        }

        $cacheKey = "github_api_{$endpoint}_" . md5(json_encode($params));

        // Check if we have cached results
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "token {$this->accessToken}"
        ])->get("https://api.github.com/{$endpoint}", $params);

        if ($response->failed()) {
            Log::error('GitHub API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new ApiException("GitHub API request failed: {$response->body()}", $response->status());
        }

        $result = $response->json();

        // Cache results for 5 minutes
        Cache::put($cacheKey, $result, 300);

        return $result;
    }
}
