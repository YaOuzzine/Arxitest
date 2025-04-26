<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestDataRequest;
use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestData;
use App\Services\TestDataService;
use App\AuthorizeResourceAccess;
use App\Models\TestCaseData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TestDataController extends Controller
{
    use AuthorizeResourceAccess;

    protected $testDataService;

    public function __construct(TestDataService $testDataService)
    {
        $this->testDataService = $testDataService;
    }
    /**
     * Display a listing of test data for a test case.
     */
    // public function index(Project $project, TestCase $test_case)
    // {
    //     $this->authorizeAccess($project);

    //     try {
    //         $testData = $this->testDataService->getAllTestData($project, $test_case);

    //         return view('dashboard.test-data.index', [
    //             'project' => $project,
    //             'testCase' => $test_case,
    //             'testSuite' => $test_case->testSuite,
    //             'testData' => $testData
    //         ]);
    //     } catch (\Exception $e) {
    //         abort(404, $e->getMessage());
    //     }
    // }

    /**
     * Store a newly created test data.
     */
    public function store(StoreTestDataRequest $request, Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        try {
            $testData = $this->testDataService->create($project, $test_case, $request->validated());

            return redirect()->route('dashboard.projects.test-cases.show', [
                'project' => $project->id,
                'test_case' => $test_case->id
            ])->with('success', 'Test data created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create test data: ' . $e->getMessage());
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

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Test case not found in this project.'
            ], 404);
        }

        $format = $request->input('format');
        $userPrompt = $request->input('prompt') ?: "Generate test data for this test case";

        // Create context for the AI
        $steps = is_array($test_case->steps) ? $test_case->steps : json_decode($test_case->steps, true);
        $stepsText = is_array($steps) ? implode("\n", array_map(fn($i, $step) => ($i+1) . ". $step", array_keys($steps), $steps)) : $test_case->steps;

        $systemPrompt = <<<PROMPT
You are an AI assistant that generates test data for software testing. Create realistic test data in {$format} format based on the following test case information:

Test Case Details:
- Title: {$test_case->title}
- Description: {$test_case->description}
- Steps:
{$stepsText}
- Expected Results: {$test_case->expected_results}

Guidelines:
1. Generate realistic, varied test data that would be useful for this test case
2. For JSON: Create a valid JSON structure with appropriate fields
3. For CSV: Format as comma-separated values with a header row
4. For XML: Create a well-formed XML document
5. For plain: Generate readable, structured text data

Your response should ONLY contain the test data in the requested format, with no explanations or markdown formatting.
PROMPT;

        try {
            $apiKey = env('OPENAI_API_KEY', config('services.openai.key'));
            $model = env('OPENAI_MODEL', config('services.openai.model', 'gpt-4o'));

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI service is not configured. Please check the API key.'
                ], 500);
            }

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
                return response()->json([
                    'success' => false,
                    'message' => 'AI generation failed. Error: ' . $response->status()
                ], 500);
            }

            $dataContent = $response->json('choices.0.message.content');

            // Clean up any possible markdown code blocks
            $dataContent = trim(preg_replace('/^```[\w]*\n|```$/m', '', $dataContent));

            $dataName = $test_case->title . ' - Test Data (' . strtoupper($format) . ')';

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
            $testCaseData = new TestCaseData();
            $testCaseData->test_case_id = $test_case->id;
            $testCaseData->test_data_id = $testData->id;
            $testCaseData->usage_context = 'Generated from AI';
            $testCaseData->save();

            return response()->json([
                'success' => true,
                'message' => 'Test data generated successfully',
                'data' => [
                    'id' => $testData->id,
                    'name' => $testData->name,
                    'content' => $testData->content,
                    'format' => $testData->format
                ]
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
     * Display the specified test data.
     */
    public function show(Project $project, TestCase $test_case, TestData $test_data)
    {
        $this->authorizeAccess($project);

        try {
            $testData = $this->testDataService->getTestData($project, $test_case, $test_data);

            return view('dashboard.test-data.show', [
                'project' => $project,
                'testCase' => $test_case,
                'testSuite' => $test_case->testSuite,
                'testData' => $testData
            ]);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
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
            $this->testDataService->detachFromTestCase($project, $test_case, $test_data);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Test data \"$dataName\" removed from this test case."
                ]);
            }

            return redirect()->route('dashboard.projects.test-cases.show', [
                'project' => $project->id,
                'test_case' => $test_case->id
            ])->with('success', "Test data \"$dataName\" removed from this test case.");
        } catch (\Exception $e) {
            Log::error('Failed to detach test data: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove test data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to remove test data: ' . $e->getMessage());
        }
    }
}
