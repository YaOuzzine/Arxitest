<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
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

        // Gemini doesn't have a system message, so we'll need to include it in the first user message
        $combinedPrompt = "System: {$systemPrompt}\n\nUser: {$userPrompt}";

        $apiKey = $this->config['api_key'];
        $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $requestBody = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $combinedPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 2000,
            ],
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(config('ai.timeout', 30))
                ->post($baseUrl, $requestBody);

            if ($response->failed()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                throw new \Exception('Gemini API Error: ' . $response->status() . ' - ' . $response->body());
            }

            $rawContent = $response->json('candidates.0.content.parts.0.text');

            if (empty($rawContent)) {
                throw new \Exception('Empty response from Gemini API');
            }

            // Process content based on expected format
            if ($outputFormat === 'json') {
                // Clean possible markdown code blocks and parse JSON
                $jsonString = trim(preg_replace('/^```[\w]*\n|```$/m', '', $rawContent));
                $data = json_decode($jsonString, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Failed to parse JSON response from Gemini');
                }

                return $data;
            }

            // For other formats, return raw content
            return ['content' => $rawContent];

        } catch (\Exception $e) {
            Log::error('Gemini Provider Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function getName(): string
    {
        return 'gemini';
    }

    public function getAvailableModels(): array
    {
        return [
            'gemini-pro',
            'gemini-pro-vision',
        ];
    }
}
