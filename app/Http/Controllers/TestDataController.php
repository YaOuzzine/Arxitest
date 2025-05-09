<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestDataRequest;
use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestData;
use App\Services\TestDataService;
use App\Traits\AuthorizeResourceAccess;
use App\Models\TestCaseData;
use App\Services\AI\AIGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\JsonResponse;

class TestDataController extends Controller
{
    use AuthorizeResourceAccess, JsonResponse;

    protected $testDataService;

    public function __construct(TestDataService $testDataService)
    {
        $this->testDataService = $testDataService;
    }

    /**
     * Store a newly created test data.
     */
    public function store(StoreTestDataRequest $request, Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        try {
            $testData = $this->testDataService->create($project, $test_case, $request->validated());

            if ($request->expectsJson()) {
                return $this->successResponse($testData, 'Test data created successfully.');
            }

            return redirect()->route('dashboard.projects.test-cases.show', [
                'project' => $project->id,
                'test_case' => $test_case->id
            ])->with('success', 'Test data created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create test data: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return $this->errorResponse('Failed to create test data: ' . $e->getMessage(), 500);
            }

            return redirect()->back()->withInput()
                ->with('error', 'Failed to create test data: ' . $e->getMessage());
        }
    }

    /**
     * Generate test data using AI.
     */
    public function generateWithAI(Request $request, Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        $validator = Validator::make($request->all(), [
            'format' => 'required|string|in:json,csv,xml,plain,other',
            'prompt' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Set up context for generation
            $context = [
                'project_id' => $project->id,
                'test_case_id' => $test_case->id,
                'test_case_title' => $test_case->title,
                'test_case_steps' => $test_case->steps,
                'test_case_expected_results' => $test_case->expected_results,
                'format' => $request->input('format')
            ];

            // Add script context if available
            $latestScript = $test_case->testScripts()->latest()->first();
            if ($latestScript) {
                $context['script_id'] = $latestScript->id;
                $context['script_content'] = $latestScript->script_content;
            }

            // Generate the test data WITHOUT saving
            $aiService = app(AIGenerationService::class);
            $testData = $aiService->generateTestData(
                $request->input('prompt', 'Generate test data for this test case'),
                $context
            );

            return response()->json([
                'success' => true,
                'message' => 'Test data generated successfully',
                'data' => $testData
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating test data with AI: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the test data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified test data.
     */
    public function update(Request $request, Project $project, TestCase $test_case, TestData $test_data)
    {
        $this->authorizeAccess($project);

        try {
            // First check if this test data is actually related to this test case
            if (!$test_case->testData()->where('test_data.id', $test_data->id)->exists()) {
                throw new \Exception('Test data not found for this test case.');
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'format' => 'required|string|in:json,csv,xml,plain,other',
                'content' => 'required|string',
                'usage_context' => 'required|string|max:255',
                'is_sensitive' => 'boolean',
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return $this->validationErrorResponse($validator);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Update the test data
            $test_data->name = $request->input('name');
            $test_data->format = $request->input('format');
            $test_data->content = $request->input('content');
            $test_data->is_sensitive = $request->boolean('is_sensitive');
            $test_data->save();

            // Update the pivot data with the new usage context
            $test_case->testData()->updateExistingPivot($test_data->id, [
                'usage_context' => $request->input('usage_context')
            ]);

            Log::info('Test data updated successfully', [
                'test_data_id' => $test_data->id,
                'test_case_id' => $test_case->id
            ]);

            if ($request->expectsJson()) {
                return $this->successResponse([
                    'id' => $test_data->id,
                    'name' => $test_data->name,
                    'format' => $test_data->format,
                    'content' => $test_data->content,
                    'is_sensitive' => $test_data->is_sensitive,
                    'pivot' => [
                        'usage_context' => $request->input('usage_context')
                    ]
                ], 'Test data updated successfully.');
            }

            return redirect()->route('dashboard.projects.test-cases.show', [
                'project' => $project->id,
                'test_case' => $test_case->id
            ])->with('success', 'Test data updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating test data: ' . $e->getMessage(), [
                'test_data_id' => $test_data->id,
                'test_case_id' => $test_case->id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return $this->errorResponse('Failed to update test data: ' . $e->getMessage(), 400);
            }

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified test data association from test case.
     */
    public function detach(Project $project, TestCase $test_case, TestData $test_data)
    {
        $this->authorizeAccess($project);

        try {
            $dataName = $test_data->name;

            // Verify the relationship
            if (!$test_case->testData()->where('test_data.id', $test_data->id)->exists()) {
                throw new \Exception('Test data not found for this test case.');
            }

            // Detach the test data
            $test_case->testData()->detach($test_data->id);

            if (request()->expectsJson()) {
                return $this->successResponse([], "Test data \"$dataName\" removed from this test case.");
            }

            return redirect()->route('dashboard.projects.test-cases.show', [
                'project' => $project->id,
                'test_case' => $test_case->id
            ])->with('success', "Test data \"$dataName\" removed from this test case.");
        } catch (\Exception $e) {
            Log::error('Failed to detach test data: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return $this->errorResponse('Failed to remove test data: ' . $e->getMessage(), 500);
            }

            return redirect()->back()->with('error', 'Failed to remove test data: ' . $e->getMessage());
        }
    }

    /**
     * Get test data content for preview.
     */
    public function getContent(Project $project, TestCase $test_case, TestData $test_data)
    {
        try {
            // Validate relationships
            $this->testDataService->validateTestDataRelationships($project, $test_case, $test_data);

            return response()->json([
                'success' => true,
                'content' => $test_data->content,
                'format' => $test_data->format,
                'name' => $test_data->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
