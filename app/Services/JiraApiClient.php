<?php

namespace App\Services;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class JiraApiClient extends ApiClient
{
    protected array $atlassianConfig;

    public function __construct()
    {
        parent::__construct();

        // Load once, so IDE sees it
        $this->atlassianConfig = config('services.atlassian', []);
    }

    protected function config(string $key): mixed
    {
        if (! Arr::has($this->atlassianConfig, $key)) {
            throw new \InvalidArgumentException("Atlassian config key [$key] is not defined.");
        }
        return $this->atlassianConfig[$key];
    }

    protected function providerName(): string
    {
        return 'Jira';
    }

    public function exchangeCode(string $code): array
    {
        return $this->request('post', '/oauth/token', [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->config('client_id'),
                'client_secret' => $this->config('client_secret'),
                'code'          => $code,
                'redirect_uri'  => $this->config('redirect'),
            ],
        ]);
    }

    public function getResources(string $accessToken): array
    {
        return $this->request('get', '/oauth/token/accessible-resources', [
            'headers' => ['Authorization' => "Bearer $accessToken"],
        ]);
    }

    public function refreshToken(string $refreshToken): array
    {
        return $this->request('post', '/oauth/token', [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => $this->config('client_id'),
                'client_secret' => $this->config('client_secret'),
                'refresh_token' => $refreshToken,
            ],
        ]);
    }

    public function api(string $cloudId, string $method, string $path, array $options = []): array
    {
        $uri = "ex/jira/{$cloudId}/" . ltrim($path, '/');
        return $this->request($method, $uri, $options);
    }
}
