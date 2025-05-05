<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class JiraApiClient extends ApiClient
{
    /**
     * Atlassian configuration array
     *
     * @var array
     */
    protected array $atlassianConfig = [];

    public function __construct()
    {
        // Initialize the config array before parent::__construct() calls config()
        $this->atlassianConfig = config('services.atlassian', []);

        parent::__construct();
    }

    /**
     * Get configuration value (public accessor)
     *
     * @param string $key
     * @return mixed
     */
    public function getConfig(string $key): mixed
    {
        return $this->config($key);
    }

    protected function config(string $key): mixed
    {
        if (!Arr::has($this->atlassianConfig, $key)) {
            throw new \InvalidArgumentException("Atlassian config key [$key] is not defined.");
        }
        return Arr::get($this->atlassianConfig, $key);
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
