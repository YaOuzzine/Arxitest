<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateScriptRequest;
use App\Http\Requests\TestScriptRequest;
use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestScript;
use App\Services\AI\AIGenerationService;
use App\Services\TestScriptService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\JsonResponse;

class TestScriptController extends Controller
{
    use JsonResponse;
    protected $testScriptService;

    public function __construct(TestScriptService $testScriptService)
    {
        $this->testScriptService = $testScriptService;
    }

    /**
     * Display a listing of test scripts for a test case.
     */
    public function index(Project $project, TestCase $test_case)
    {
        try {
            $this->testScriptService->validateRelationships($project, $test_case);
            $testScripts = $test_case->testScripts()->orderBy('created_at', 'desc')->get();

            return view('dashboard.test-scripts.index', [
                'project' => $project,
                'testCase' => $test_case,
                'testSuite' => $test_case->testSuite,
                'testScripts' => $testScripts
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Store a newly created test script.
     */
    public function store(TestScriptRequest $request, Project $project, TestCase $test_case)
    {
        try {
            $this->testScriptService->validateRelationships($project, $test_case);

            // Create script using the service
            $testScript = $this->testScriptService->createScript(
                $test_case,
                $request->input('name'),
                $request->input('framework_type'),
                $request->input('script_content')
            );

            return redirect()->route('dashboard.projects.test-cases.show', [
                'project' => $project->id,
                'test_case' => $test_case->id
            ])->with('success', 'Test script created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Generate a test script using AI.
     */
    public function generateWithAI(Request $request, Project $project, TestCase $test_case)
    {
        $validator = Validator::make($request->all(), [
            'framework_type' => 'required|string|in:selenium-python,cypress,other',
            'prompt' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check relationships
            $this->testScriptService->validateRelationships($project, $test_case);

            // Set up context
            $context = [
                'project_id' => $project->id,
                'test_case_id' => $test_case->id,
                'test_case_title' => $test_case->title,
                'test_case_steps' => $test_case->steps,
                'test_case_expected_results' => $test_case->expected_results,
                'framework_type' => $request->input('framework_type')
            ];

            // Generate the test script
            $aiService = app(AIGenerationService::class);
            $testScript = $aiService->generateTestScript(
                $request->input('prompt', ''),
                $context
            );

            return response()->json([
                'success' => true,
                'message' => 'Test script generated successfully',
                'script' => [
                    'id' => $testScript->id,
                    'name' => $testScript->name,
                    'content' => $testScript->script_content,
                    'framework_type' => $testScript->framework_type
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating test script with AI: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the test script: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified test script.
     */
    public function show(Project $project, TestCase $test_case, TestScript $test_script)
    {
        try {
            $this->testScriptService->validateRelationships($project, $test_case, $test_script);

            return view('dashboard.test-scripts.show', [
                'project' => $project,
                'testCase' => $test_case,
                'testSuite' => $test_case->testSuite,
                'testScript' => $test_script
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified test script.
     */
    public function destroy(Project $project, TestCase $test_case, TestScript $test_script)
    {
        try {
            $this->testScriptService->validateRelationships($project, $test_case, $test_script);

            $scriptName = $test_script->name;
            $test_script->delete();

            if (request()->expectsJson()) {
                return $this->successResponse([], "Test script \"$scriptName\" deleted successfully.");
            }

            return redirect()->route('dashboard.projects.test-cases.show', [
                'project' => $project->id,
                'test_case' => $test_case->id
            ])->with('success', "Test script \"$scriptName\" deleted successfully.");
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return $this->errorResponse($e->getMessage(), 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}
