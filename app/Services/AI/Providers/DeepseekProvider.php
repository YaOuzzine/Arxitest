<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepseekProvider implements AIProviderInterface
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
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Content-Type' => 'application/json',
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

        // Set response format for specific output types
        if ($outputFormat === 'json') {
            $requestBody['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(config('ai.timeout', 60))
                ->post($this->config['chat_url'], $requestBody);

            if ($response->failed()) {
                Log::error('Deepseek API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                throw new \Exception('Deepseek API Error: ' . $response->status() . ' - ' . $response->body());
            }

            $content = $response->json('choices.0.message.content');

            // Process content based on expected format
            if ($outputFormat === 'json') {
                // Clean possible markdown code blocks and parse JSON
                $jsonString = trim(preg_replace('/^```[\w]*\n|```$/m', '', $content));
                $data = json_decode($jsonString, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Failed to parse JSON response from Deepseek');
                }

                return $data;
            }

            // For other formats, return raw content
            return ['content' => $content];

        } catch (\Exception $e) {
            Log::error('Deepseek Provider Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function getName(): string
    {
        return 'deepseek';
    }

    public function getAvailableModels(): array
    {
        return [
            'deepseek-chat',
            'deepseek-coder',
        ];
    }
}
