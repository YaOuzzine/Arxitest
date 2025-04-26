<?php

namespace App\Services;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestData;
use App\Models\TestCaseData;
use App\Services\RelationshipValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestDataService
{
    protected $relationshipValidator;

    public function __construct(RelationshipValidationService $relationshipValidator)
    {
        $this->relationshipValidator = $relationshipValidator;
    }

    /**
     * Get test data for a test case, ensuring valid relationships.
     */
    public function getTestData(Project $project, TestCase $testCase, TestData $testData): TestData
    {
        // Validate relationships first
        $this->relationshipValidator->validateTestDataRelationships($project, $testCase, $testData);

        return $testData;
    }

    /**
     * Get all test data for a test case, ensuring valid relationships.
     */
    public function getAllTestData(Project $project, TestCase $testCase)
    {
        // Validate relationships first
        $this->relationshipValidator->validateProjectTestCaseRelationship($project, $testCase);

        return $testCase->testData()->get();
    }

    /**
     * Create a new test data and associate it with a test case.
     */
    public function create(Project $project, TestCase $testCase, array $data): TestData
    {
        // Validate relationships first
        $this->relationshipValidator->validateProjectTestCaseRelationship($project, $testCase);

        // Create the test data record
        $testData = new TestData();
        $testData->name = $data['name'];
        $testData->content = $data['content'];
        $testData->format = $data['format'];
        $testData->is_sensitive = $data['is_sensitive'] ?? false;
        $testData->metadata = [
            'created_by' => Auth::id(),
            'created_through' => 'manual'
        ];
        $testData->save();

        // Create the relationship to the test case
        $this->attachToTestCase($testCase, $testData, $data['usage_context'] ?? null);

        return $testData;
    }

    /**
     * Attach test data to a test case.
     */
    public function attachToTestCase(TestCase $testCase, TestData $testData, ?string $usageContext = null): void
    {
        $pivotData = new \App\Models\TestCaseData();
        $pivotData->test_case_id = $testCase->id;
        $pivotData->test_data_id = $testData->id;
        $pivotData->usage_context = $usageContext;
        $pivotData->save();
    }

    /**
     * Detach test data from a test case.
     */
    public function detachFromTestCase(Project $project, TestCase $testCase, TestData $testData): bool
    {
        // Validate relationships first
        $this->relationshipValidator->validateTestDataRelationships($project, $testCase, $testData);

        return $testCase->testData()->detach($testData->id) > 0;
    }

    /**
     * Generate test data using AI.
     */
    public function generateWithAI(Project $project, TestCase $testCase, string $format, string $prompt = ''): array
    {
        // Validate relationships first
        $this->relationshipValidator->validateProjectTestCaseRelationship($project, $testCase);

        try {
            $apiKey = env('OPENAI_API_KEY', config('services.openai.key'));
            $model = env('OPENAI_MODEL', config('services.openai.model', 'gpt-4o'));

            if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'AI service is not configured. Please check the API key.'
                ];
            }

            // Create context for the AI
            $steps = is_array($testCase->steps) ? $testCase->steps : json_decode($testCase->steps, true);
            $stepsText = is_array($steps)
                ? implode("\n", array_map(fn($i, $step) => ($i+1) . ". $step", array_keys($steps), $steps))
                : $testCase->steps;

            $systemPrompt = $this->buildAISystemPrompt($testCase, $format, $stepsText);
            $userPrompt = $prompt ?: "Generate test data for this test case";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                Log::error('AI generation failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'AI generation failed. Error: ' . $response->status()
                ];
            }

            $dataContent = $response->json('choices.0.message.content');

            // Clean up any possible markdown code blocks
            $dataContent = trim(preg_replace('/^```[\w]*\n|```$/m', '', $dataContent));

            $dataName = $testCase->title . ' - Test Data (' . strtoupper($format) . ')';

            // Create the test data
            $testData = new TestData();
            $testData->name = $dataName;
            $testData->content = $dataContent;
            $testData->format = $format;
            $testData->is_sensitive = false; // Default to non-sensitive
            $testData->metadata = [
                'created_by' => Auth::id(),
                'created_through' => 'ai',
                'source' => 'openai',
                'model' => $model,
                'prompt' => $userPrompt
            ];
            $testData->save();

            // Create the relationship to the test case
            $this->attachToTestCase($testCase, $testData, 'Generated from AI');

            return [
                'success' => true,
                'message' => 'Test data generated successfully',
                'data' => [
                    'id' => $testData->id,
                    'name' => $testData->name,
                    'content' => $testData->content,
                    'format' => $testData->format
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error generating test data with AI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build the system prompt for AI test data generation.
     */
    private function buildAISystemPrompt(TestCase $testCase, string $format, string $stepsText): string
    {
        return <<<PROMPT
You are an AI assistant that generates test data for software testing. Create realistic test data in {$format} format based on the following test case information:

Test Case Details:
- Title: {$testCase->title}
- Description: {$testCase->description}
- Steps:
{$stepsText}
- Expected Results: {$testCase->expected_results}

Guidelines:
1. Generate realistic, varied test data that would be useful for this test case
2. For JSON: Create a valid JSON structure with appropriate fields
3. For CSV: Format as comma-separated values with a header row
4. For XML: Create a well-formed XML document
5. For plain: Generate readable, structured text data

Your response should ONLY contain the test data in the requested format, with no explanations or markdown formatting.
PROMPT;
    }
}
