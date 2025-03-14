<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TestScriptGenerationController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Generate a test script using OpenAI
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateWithOpenAI(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'framework_type' => 'required|string|in:selenium_python,cypress',
                'context' => 'required|array',
                'context.jira_story_id' => 'nullable|exists:jira_stories,id',
                'context.user_stories' => 'nullable|array',
                'context.user_stories.*' => 'exists:jira_stories,id',
                'context.project_description' => 'nullable|string',
                'context.custom_instructions' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Process file uploads if needed
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                $fileContents = [];

                foreach ($files as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $name = $file->getClientOriginalName();
                    $content = file_get_contents($file->getRealPath());

                    // Add file content to the context
                    $fileContents[] = [
                        'name' => $name,
                        'extension' => $extension,
                        'content' => $content
                    ];
                }

                // Add file contents to the context information
                $request->merge([
                    'context' => array_merge($request->context, ['files' => $fileContents])
                ]);
            }

            // Generate test script
            $result = $this->openAIService->generateTestScript($request->all());

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error in test script generation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during test script generation: ' . $e->getMessage()
            ], 500);
        }
    }
}
