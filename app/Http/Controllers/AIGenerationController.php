<?php

namespace App\Http\Controllers;

use App\Http\Requests\AIGenerationRequest;
use App\Services\AI\AIGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AIGenerationController extends Controller
{
    protected AIGenerationService $aiService;

    public function __construct(AIGenerationService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Generate AI content for a specific entity type
     *
     * @param AIGenerationRequest $request
     * @param string $entityType
     * @return JsonResponse
     */
    public function generate(AIGenerationRequest $request, string $entityType): JsonResponse
    {
        try {
            // Normalize entity type (kebab-case to snake_case)
            $normalizedType = str_replace('-', '_', $entityType);
            Log::debug("HERE");

            // Get the context from the request
            $context = $request->context();
            Log::debug("HERE2");
            // Optional override of provider
            if ($request->has('provider')) {
                $this->aiService->setProvider($request->input('provider'));
                Log::debug("HERE3");
            }
            Log::debug("HERE4: {$normalizedType}");
            // Call the appropriate method based on entity type
            $result = match ($normalizedType) {
                'story' => $this->aiService->generateStory($request->input('prompt'), $context),
                'test_case' => $this->aiService->generateTestCase($request->input('prompt'), $context),
                'test_suite' => $this->aiService->generateTestSuite($request->input('prompt'), $context),
                'test_script' => $this->aiService->generateTestScript($request->input('prompt'), $context),
                'test_data' => $this->aiService->generateTestData($request->input('prompt'), $context),
                default => throw new \InvalidArgumentException("Unsupported entity type: {$entityType}")
            };
            $data = $this->formatResponse($result, $normalizedType);

            Log::debug("HERE5");

            // Return appropriate response based on entity type
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format the response based on entity type
     *
     * @param mixed $result
     * @param string $entityType
     * @return array
     */
    private function formatResponse($result, string $entityType): array
    {
        Log::debug('formatResponse called with:', [
            'result_type' => gettype($result),
            'result' => $result,
            'entity_type' => $entityType
        ]);
        return match ($entityType) {
            'story' => [
                // Story doesn't have .id in the AI response
                'title' => $result['title'] ?? '',
                'description' => $result['description'] ?? '',
                'acceptance_criteria' => $result['metadata']['acceptance_criteria'] ?? [],
                'priority' => $result['metadata']['priority'] ?? 'medium',
                'tags' => $result['metadata']['tags'] ?? [],
            ],
            'test_case' => [
                // Test case doesn't have .id in the AI response
                'title' => $result['title'] ?? '',
                'description' => $result['description'] ?? '',
                'steps' => $result['steps'] ?? [],
                'expected_results' => $result['expected_results'] ?? '',
                'priority' => $result['priority'] ?? 'medium',
                'status' => $result['status'] ?? 'draft',
                'tags' => $result['tags'] ?? [],
            ],
            'test_suite' => [
                // Test suite doesn't have .id in the AI response
                'name' => $result['name'] ?? '',
                'description' => $result['description'] ?? '',
                'settings' => $result['settings'] ?? [],
            ],
            'test_script' => [
                // Test script doesn't have .id in the AI response
                'name' => $result['name'] ?? '',
                'content' => $result['content'] ?? '',
                'framework_type' => $result['framework_type'] ?? '',
            ],
            'test_data' => [
                // Test data doesn't have .id in the AI response
                'name' => $result['name'] ?? '',
                'content' => $result['content'] ?? '',
                'format' => $result['format'] ?? '',
            ],
            default => (array) $result,
        };
    }
}
