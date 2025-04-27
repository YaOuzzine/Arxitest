<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider implements AIProviderInterface
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

        $headers = [
            'x-api-key' => $this->config['api_key'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Anthropic-Version' => '2023-06-01'
        ];

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $requestBody = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => 2000,
        ];

        // Format for specific output types
        if ($outputFormat === 'json') {
            $requestBody['system'] = $systemPrompt;
            $requestBody['messages'] = [['role' => 'user', 'content' => $userPrompt]];
            $requestBody['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(config('ai.timeout', 30))
                ->post('https://api.anthropic.com/v1/messages', $requestBody);

            if ($response->failed()) {
                Log::error('Claude API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                throw new \Exception('Claude API Error: ' . $response->status() . ' - ' . $response->body());
            }

            $content = $response->json('content.0.text');

            // Process content based on expected format
            if ($outputFormat === 'json') {
                // Clean possible markdown code blocks and parse JSON
                $jsonString = trim(preg_replace('/^```[\w]*\n|```$/m', '', $content));
                $data = json_decode($jsonString, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Failed to parse JSON response from Claude');
                }

                return $data;
            }

            // For other formats, return raw content
            return ['content' => $content];

        } catch (\Exception $e) {
            Log::error('Claude Provider Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function getName(): string
    {
        return 'claude';
    }

    public function getAvailableModels(): array
    {
        return [
            'claude-3-opus-20240229',
            'claude-3-sonnet-20240229',
            'claude-3-haiku-20240307',
            'claude-2.1',
            'claude-2.0',
            'claude-instant-1.2',
        ];
    }
}
