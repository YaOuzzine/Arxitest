<?php

namespace App\Services\AI\Providers;

interface AIProviderInterface
{
    /**
     * Generate a completion from the AI provider
     *
     * @param string $prompt The prompt to send to the AI
     * @param array $options Additional options specific to this generation
     * @return array The AI response
     */
    public function generate(string $prompt, array $options = []): array;

    /**
     * Get the name of the provider
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get available models for this provider
     *
     * @return array
     */
    public function getAvailableModels(): array;
}
