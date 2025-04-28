<?php

namespace App\Services\AI;

use App\Services\AI\Providers\AIProviderInterface;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\DeepseekProvider;
use App\Services\AI\Providers\GeminiProvider;
use Illuminate\Support\Facades\Log;

class AIGenerationService
{
    protected AIProviderInterface $provider;

    /**
     * Create a new AI Generation service instance
     *
     * @param string|null $providerName Provider to use (or use default from config)
     */
    public function __construct(?string $providerName = null)
    {
        $providerName = $providerName ?? config('ai.default_provider');
        $this->setProvider($providerName);
    }

    /**
     * Set the AI provider to use
     *
     * @param string $providerName
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setProvider(string $providerName): self
    {
        $this->provider = match (strtolower($providerName)) {
            'openai' => new OpenAIProvider(config('ai.providers.openai')),
            'claude' => new ClaudeProvider(config('ai.providers.claude')),
            'deepseek' => new DeepseekProvider(config('ai.providers.deepseek')),
            'gemini' => new GeminiProvider(config('ai.providers.gemini')),
            default => throw new \InvalidArgumentException("Unsupported AI provider: {$providerName}")
        };

        return $this;
    }

    /**
     * Generate content for a specific entity type
     *
     * @param string $entityType The type of entity (story, test-case, etc.)
     * @param string $prompt User's prompt input
     * @param array $context Additional context (e.g. project info)
     * @return array Generated result
     */
    public function generate(string $entityType, string $prompt, array $context = []): array
    {
        try {
            // Get the system prompt based on entity type
            $systemPrompt = $this->getSystemPrompt($entityType, $context);

            // Generate with the selected provider
            return $this->provider->generate($systemPrompt, [
                'user_prompt' => $prompt,
                'context' => $context,
                // Add any entity-specific options
                'output_format' => $this->getOutputFormat($entityType)
            ]);
        } catch (\Exception $e) {
            Log::error("AI Generation failed for {$entityType}", [
                'error' => $e->getMessage(),
                'provider' => $this->provider->getName(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get the system prompt for a specific entity type
     *
     * @param string $entityType
     * @param array $context
     * @return string
     */
    protected function getSystemPrompt(string $entityType, array $context = []): string
    {
        return match ($entityType) {
            'story' => \App\Services\AI\Prompts\StoryPrompts::getSystemPrompt($context),
            'test-case' => \App\Services\AI\Prompts\TestCasePrompts::getSystemPrompt($context),
            'test-suite' => \App\Services\AI\Prompts\TestSuitePrompts::getSystemPrompt($context),
            'test-script' => \App\Services\AI\Prompts\TestScriptPrompts::getSystemPrompt($context),
            'test-data' => \App\Services\AI\Prompts\TestDataPrompts::getSystemPrompt($context),
            default => throw new \InvalidArgumentException("Unsupported entity type: {$entityType}")
        };
    }

    /**
     * Get the expected output format for the entity type
     *
     * @param string $entityType
     * @return string
     */
    protected function getOutputFormat(string $entityType): string
    {
        return match ($entityType) {
            'story', 'test-case', 'test-suite' => 'json',
            'test-script' => 'code',
            'test-data' => 'data',
            default => 'text'
        };
    }

    /**
     * Get the current provider
     *
     * @return AIProviderInterface
     */
    public function getProvider(): AIProviderInterface
    {
        return $this->provider;
    }


    /**
     * Generate a story and create a record in the database
     *
     * @param string $prompt User's prompt
     * @param array $context Context data
     * @return \App\Models\Story
     */
    public function generateStory(string $prompt, array $context): array
    {
        $storyData = $this->generate('story', $prompt, $context);

        // Return data without saving
        return [
            'project_id' => $context['project_id'],
            'title' => $storyData['title'] ?? 'Generated Story',
            'description' => $storyData['description'] ?? '',
            'source' => 'manual',
            'epic_id' => $context['epic_id'] ?? null,
            'metadata' => [
                'created_by' => auth()->id(),
                'created_through' => 'ai',
                'source' => $this->provider->getName(),
                'prompt' => $prompt,
                'acceptance_criteria' => $storyData['acceptance_criteria'] ?? [],
                'priority' => $storyData['priority'] ?? 'medium',
                'tags' => $storyData['tags'] ?? []
            ]
        ];
    }
    /**
     * Generate a test case and create a record in the database
     *
     * @param string $prompt User's prompt
     * @param array $context Context data
     * @return \App\Models\TestCase
     */
    public function generateTestCase(string $prompt, array $context): \App\Models\TestCase
    {
        $testCaseData = $this->generate('test-case', $prompt, $context);

        // Create the TestCase record
        $testCase = new \App\Models\TestCase();
        $testCase->suite_id = $context['suite_id'] ?? null;
        $testCase->story_id = $context['story_id'] ?? null;
        $testCase->title = $testCaseData['title'] ?? 'Generated Test Case';
        $testCase->description = $testCaseData['description'] ?? '';
        $testCase->steps = $testCaseData['steps'] ?? [];
        $testCase->expected_results = $testCaseData['expected_results'] ?? '';
        $testCase->priority = $testCaseData['priority'] ?? 'medium';
        $testCase->status = 'draft';
        $testCase->tags = $testCaseData['tags'] ?? [];
        $testCase->save();

        return $testCase;
    }

    /**
     * Generate a test script and create a record in the database
     *
     * @param string $prompt User's prompt
     * @param array $context Context data
     * @return \App\Models\TestScript
     */
    public function generateTestScript(string $prompt, array $context): \App\Models\TestScript
    {
        $result = $this->generate('test-script', $prompt, $context);
        $scriptContent = $result['content'] ?? '';

        // Create a name for the script
        $testCase = \App\Models\TestCase::find($context['test_case_id']);
        $frameworkType = $context['framework_type'] ?? 'selenium-python';
        $scriptName = ($testCase ? $testCase->title : 'Generated') . ' - ' . ucfirst($frameworkType) . ' Script';

        // Create and save the script
        $testScript = new \App\Models\TestScript();
        $testScript->test_case_id = $context['test_case_id'];
        $testScript->creator_id = auth()->id();
        $testScript->name = $scriptName;
        $testScript->framework_type = $frameworkType;
        $testScript->script_content = $scriptContent;
        $testScript->metadata = [
            'created_through' => 'ai',
            'source' => $this->provider->getName(),
            'prompt' => $prompt
        ];
        $testScript->save();

        return $testScript;
    }

    /**
     * Generate test data and create a record in the database
     *
     * @param string $prompt User's prompt
     * @param array $context Context data
     * @return \App\Models\TestData
     */
    public function generateTestData(string $prompt, array $context): \App\Models\TestData
    {
        $result = $this->generate('test-data', $prompt, $context);
        $dataContent = $result['content'] ?? '';

        // Get test case for naming
        $testCase = \App\Models\TestCase::find($context['test_case_id']);
        $format = $context['format'] ?? 'json';
        $dataName = ($testCase ? $testCase->title : 'Generated') . ' - Test Data (' . strtoupper($format) . ')';

        // Create the test data
        $testData = new \App\Models\TestData();
        $testData->name = $dataName;
        $testData->content = $dataContent;
        $testData->format = $format;
        $testData->is_sensitive = false; // Default to non-sensitive
        $testData->metadata = [
            'created_by' => auth()->id(),
            'created_through' => 'ai',
            'source' => $this->provider->getName(),
            'prompt' => $prompt
        ];
        $testData->save();

        // Create relationship to test case if provided
        if ($testCase) {
            $testCaseData = new \App\Models\TestCaseData();
            $testCaseData->test_case_id = $testCase->id;
            $testCaseData->test_data_id = $testData->id;
            $testCaseData->usage_context = 'Generated from AI';
            $testCaseData->save();
        }

        return $testData;
    }

    /**
     * Generate a test suite and create a record in the database
     *
     * @param string $prompt User's prompt
     * @param array $context Context data
     * @return \App\Models\TestSuite
     */
    public function generateTestSuite(string $prompt, array $context): \App\Models\TestSuite
    {
        $suiteData = $this->generate('test-suite', $prompt, $context);

        // Create the test suite
        $suite = new \App\Models\TestSuite();
        $suite->project_id = $context['project_id'];
        $suite->name = $suiteData['name'] ?? 'Generated Test Suite';
        $suite->description = $suiteData['description'] ?? '';

        // Set settings with defaults
        $settings = [
            'default_priority' => $suiteData['settings']['default_priority'] ?? 'medium',
            'execution_mode' => $suiteData['settings']['execution_mode'] ?? 'sequential',
        ];
        $suite->settings = $settings;

        $suite->save();

        return $suite;
    }
}
