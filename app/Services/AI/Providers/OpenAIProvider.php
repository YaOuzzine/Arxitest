<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function generate(string $systemPrompt, array $options = []): array
    {
        $userPrompt = $options['user_prompt'] ?? '';
        $model = $options['model'] ?? $this->config['model'];
        $temperature = $options['temperature'] ?? $this->config['temperature'];
        $outputFormat = $options['output_format'] ?? 'text';
        Log::debug("IN THE PROVIDER");
        $responseFormat = null;
        if ($outputFormat === 'json') {
            $responseFormat = ['type' => 'json_object'];
        }
        Log::debug("Getting header");
        $headers = [
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Content-Type' => 'application/json',
        ];
        Log::debug("getting messages");
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];
        Log::debug("THE BODY");
        $requestBody = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => 2000,
        ];

        Log::debug("Request Body");

        if ($responseFormat) {
            $requestBody['response_format'] = $responseFormat;
        }

        try {

            $response = Http::withHeaders($headers)
                ->timeout(config('ai.timeout', 30))
                ->post('https://api.openai.com/v1/chat/completions', $requestBody);

            Log::debug("Response: {$response}");
            if ($response->failed()) {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                throw new \Exception('OpenAI API Error: ' . $response->status() . ' - ' . $response->body());
            }

            $content = $response->json('choices.0.message.content');

            // Process content based on expected format
            if ($outputFormat === 'json') {
                // Clean possible markdown code blocks and parse JSON
                $jsonString = trim(preg_replace('/^```[\w]*\n|```$/m', '', $content));
                $data = json_decode($jsonString, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Failed to parse JSON response from OpenAI');
                }

                return $data;
            }

            // For other formats, return raw content
            return ['content' => $content];

        } catch (\Exception $e) {
            Log::error('OpenAI Provider Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function getAvailableModels(): array
    {
        return [
            'gpt-4o',
            'gpt-4-turbo',
            'gpt-4',
            'gpt-3.5-turbo',
        ];
    }
}
