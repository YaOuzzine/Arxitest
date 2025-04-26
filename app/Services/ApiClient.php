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
        $this->baseUri = rtrim($this->config('base_uri'), '/');
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

        try {
            $response = Http::withHeaders($this->defaultHeaders)
                            ->$method($url, $options)
                            ->throw(); // will throw RequestException on 4xx/5xx

            return $response->json();
        } catch (RequestException $e) {
            Log::error("{$this->providerName()} API error", [
                'method' => strtoupper($method),
                'url'    => $url,
                'body'   => $e->response?->body(),
                'error'  => $e->getMessage(),
            ]);
            throw new ApiException(
                "{$this->providerName()} API request failed: " . $e->getMessage(),
                $e->response?->status() ?? 0,
                $e
            );
        } catch (Throwable $e) {
            Log::error("{$this->providerName()} HTTP error", [
                'method' => strtoupper($method),
                'url'    => $url,
                'error'  => $e->getMessage(),
            ]);
            throw new ApiException("Unexpected HTTP error: " . $e->getMessage(), 0, $e);
        }
    }

    /** Override to provide nice name */
    protected function providerName(): string
    {
        return static::class;
    }
}
