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
     * Get base URI for Atlassian API
     *
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->atlassianConfig['base_uri'] ?? 'https://auth.atlassian.com';
    }

    /**
     * Get client ID for Atlassian API
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->atlassianConfig['client_id'] ?? '';
    }

    /**
     * Get client secret for Atlassian API
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->atlassianConfig['client_secret'] ?? '';
    }

    /**
     * Get redirect URI for Atlassian API
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->atlassianConfig['redirect'] ?? '';
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
        $url = rtrim($this->config('base_uri'), '/') . '/oauth/token';

        try {
            $response = Http::asForm()->post($url, [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->config('client_id'),
                'client_secret' => $this->config('client_secret'),
                'code'          => $code,
                'redirect_uri'  => $this->config('redirect'),
            ]);

            $response->throw();
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Jira token exchange failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw new ApiException('Failed to exchange code for token: ' . $e->getMessage(), 400, $e);
        }
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


    public function getResources(string $accessToken): array
    {
        return Http::withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => "Bearer $accessToken",
        ])->get('https://api.atlassian.com/oauth/token/accessible-resources')
            ->throw()        // optional: ensures exceptions on 4xx/5xx
            ->json();
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
