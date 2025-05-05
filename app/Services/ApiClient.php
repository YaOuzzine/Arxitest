<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class ApiClient
{
    /** @var string */
    protected $baseUri;

    /** @var array */
    protected $defaultHeaders = ['Accept' => 'application/json'];

    public function __construct()
    {
        $this->baseUri = rtrim($this->config('api_uri'), '/');
    }

    /** Get provider-specific config */
    abstract protected function config(string $key): mixed;

    /**
     * Make a request and wrap errors in a uniform exception.
     *
     * @throws ApiException
     */
    protected function request(string $method, string $uri, array $options = []): array
    {
        $url = $this->baseUri . '/' . ltrim($uri, '/');

        // Merge default and custom headers
        $headers = array_merge(
            $this->defaultHeaders,
            $options['headers'] ?? []
        );
        $httpRequest = Http::withHeaders($headers);

        // Remove headers from options before sending body/query
        unset($options['headers']);

        // Handle form parameters vs. direct payload
        if (isset($options['form_params'])) {
            $form = $options['form_params'];
            unset($options['form_params']);
            $response = $httpRequest->asForm()->{$method}($url, $form);
        } else {
            $response = $httpRequest->{$method}($url, $options);
        }

        $response->throw();
        return $response->json();
    }


    /** Override to provide nice name */
    protected function providerName(): string
    {
        return static::class;
    }
}
