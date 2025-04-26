<?php

namespace App\Services;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestScript;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestScriptService
{
    /**
     * Validate relationships between project, test case, and test script.
     */
    public function validateRelationships(Project $project, TestCase $testCase, ?TestScript $testScript = null): bool
    {
        // Validate project-test case relationship
        $suite = $testCase->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            throw new \Exception('Test case not found in this project.');
        }

        // If test script is provided, validate test case-test script relationship
        if ($testScript && $testScript->test_case_id !== $testCase->id) {
            throw new \Exception('Test script not found in this test case.');
        }

        return true;
    }

     /**
     * Create a new test script.
     */
    public function createScript(
        TestCase $testCase,
        string $name,
        string $frameworkType,
        string $scriptContent,
        array $metadata = []
    ): TestScript {
        $defaultMetadata = [
            'created_through' => 'manual',
            'source' => 'user'
        ];

        $testScript = new TestScript();
        $testScript->test_case_id = $testCase->id;
        $testScript->creator_id = Auth::id();
        $testScript->name = $name;
        $testScript->framework_type = $frameworkType;
        $testScript->script_content = $scriptContent;
        $testScript->metadata = array_merge($defaultMetadata, $metadata);
        $testScript->save();

        return $testScript;
    }

    /**
     * Generate a test script using AI.
     */
    public function generateWithAI(TestCase $testCase, string $framework, string $prompt = ''): string
    {
        $apiKey = env('OPENAI_API_KEY', config('services.openai.key'));
        $model = env('OPENAI_MODEL', config('services.openai.model', 'gpt-4o'));

        if (!$apiKey) {
            throw new \Exception('AI service is not configured. Please check the API key.');
        }

        // Build the system prompt
        $systemPrompt = $this->getScriptGenerationSystemPrompt($framework);

        // Build the user prompt
        $userPrompt = $prompt ?: "Generate a test script based on the test case";
        $fullPrompt = "Generate a test script for the following test case:\n\n";
        $fullPrompt .= "Title: {$testCase->title}\n";
        $fullPrompt .= "Steps:\n" . implode("\n", $testCase->steps) . "\n";
        $fullPrompt .= "Expected Results: {$testCase->expected_results}\n";
        $fullPrompt .= $userPrompt;

        // Call the OpenAI API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $fullPrompt],
            ],
            'temperature' => 0.7,
        ]);

        if (!$response->successful()) {
            Log::error('AI script generation failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("AI generation failed: " . $response->status());
        }

        $scriptContent = $response->json('choices.0.message.content');

        // Clean up any possible markdown code blocks
        return trim(preg_replace('/^```[\w]*\n|```$/m', '', $scriptContent));
    }

    /**
     * Get the system prompt for script generation.
     */
    private function getScriptGenerationSystemPrompt(string $framework): string
    {
        // Same implementation as in controller
        return <<<PROMPT
You are an AI assistant that specializes in generating test automation scripts. You'll create a test script based on the provided test case information.

Create a complete, working test script using the {$framework} framework. Do not abbreviate or omit parts of the code - generate a complete test script that could be executed.

Follow these specific guidelines:
1. For selenium-python:
   - Use Selenium WebDriver with Python
   - Include proper imports, setup, teardown
   - Use best practices like explicit waits
   - Structure as a proper Python test using unittest or pytest

2. For cypress:
   - Use Cypress JavaScript syntax
   - Include proper imports, before/after hooks
   - Follow Cypress best practices
   - Structure as a proper Cypress test

Your response should be ONLY the code for the test script, without explanations, markdown formatting, or code block markers.
PROMPT;
    }
}
