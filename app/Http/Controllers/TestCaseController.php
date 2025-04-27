<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestCaseRequest;
use App\Http\Requests\TestCaseIndexRequest;
use App\Models\Project;
use App\Models\Team;
use App\Models\TestCase;
use App\Models\TestSuite;
use App\Services\TestCaseService;
use App\Services\RelationshipValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\JsonResponse;
use App\Services\StoryService;

class TestCaseController extends Controller
{
    use JsonResponse;
    protected $testCaseService;
    protected $relationshipValidationService;
    protected $storyService;

    public function __construct(StoryService $storyService, TestCaseService $testCaseService, RelationshipValidationService $relationshipValidationService)
    {
        $this->testCaseService = $testCaseService;
        $this->storyService = $storyService;
        $this->relationshipValidationService = $relationshipValidationService;
    }

    /**
     * Authorization check (temporarily disabled like other controllers)
     */
    private function authorizeAccess($project): void
    {
        Log::warning('AUTHORIZATION CHECK IS TEMPORARILY DISABLED in TestCaseController@authorizeAccess');
    }

    /**
     * Display a listing of all test cases across all projects (with filtering).
     */
    public function indexAll(TestCaseIndexRequest $request)
    {
        // Get the current team context (using the trait)
        $team = $this->getCurrentTeam($request);
        if (!$team) {
            return redirect()->route('dashboard.select-team')->with('error', 'Please select a team.');
        }

        // Get validated filters from the request object
        $filters = $request->filters();

        try {
            // Call the service to get filtered data
            $viewData = $this->testCaseService->getFilteredTestCasesForTeam($team, $filters);
            // dd($viewData);
            // Pass all necessary data to the view
            return view('dashboard.test-cases.index', [
                'testCases'         => $viewData['testCases'],
                'projectsForFilter' => $viewData['projectsForFilter'],
                'storiesForFilter'  => $viewData['storiesForFilter'] ?? collect(),
                'suitesForFilter'   => $viewData['suitesForFilter'] ?? collect(),
                'team'              => $team,
                'isGenericIndex'    => true, // Flag for the view
                'selectedProjectId' => $filters['project_id'] ?? null,
                'selectedStoryId'   => $filters['story_id'] ?? null,
                'selectedSuiteId'   => $filters['suite_id'] ?? null,
                'searchTerm'        => $filters['search'] ?? '',
                'sortField'         => $filters['sort'] ?? 'updated_at',
                'sortDirection'     => $filters['direction'] ?? 'desc',
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching test cases for team {$team->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Could not load test cases. Please try again.');
        }
    }

    /**
     * Display a listing of the test cases for a specific project or test suite.
     */
    public function index(Request $request, Project $project, ?TestSuite $test_suite = null)
    {
        $this->authorizeAccess($project);

        // Preload filter collections
        $storiesForFilter = $project
            ->stories()
            ->orderBy('title')
            ->get(['id', 'title']);
        $suitesForFilter = $project
            ->testSuites()
            ->orderBy('name')
            ->get(['id', 'name']);

        try {
            if ($test_suite) {
                // Suite-specific listing
                Log::debug("Loading test cases for specific suite", ['suite_id' => $test_suite->id]);
                $data = $this->testCaseService->getTestCasesForSuite(
                    $project,
                    $test_suite,
                    [
                        'search'    => $request->input('search'),
                        'sort'      => $request->input('sort', 'updated_at'),
                        'direction' => $request->input('direction', 'desc'),
                    ]
                );

                return view('dashboard.test-cases.index', array_merge($data, [
                    'project'           => $project,
                    'testSuite'         => $test_suite,
                    'storiesForFilter'  => $storiesForFilter,
                    'suitesForFilter'   => $suitesForFilter,
                    'searchTerm'        => $request->input('search', ''),
                    'sortField'         => $request->input('sort', 'updated_at'),
                    'sortDirection'     => $request->input('direction', 'desc'),
                ]));
            } else {
                // Project-wide listing
                Log::debug("Loading test cases for entire project");
                $data = $this->testCaseService->getTestCasesForProject(
                    $project,
                    [
                        'suite_id'  => $request->input('suite_id'),
                        'search'    => $request->input('search'),
                        'sort'      => $request->input('sort', 'updated_at'),
                        'direction' => $request->input('direction', 'desc'),
                    ]
                );

                return view('dashboard.test-cases.index', array_merge($data, [
                    'project'           => $project,
                    'storiesForFilter'  => $storiesForFilter,
                    'suitesForFilter'   => $suitesForFilter,
                    'selectedSuiteId'   => $request->input('suite_id'),
                    'searchTerm'        => $request->input('search', ''),
                    'sortField'         => $request->input('sort', 'updated_at'),
                    'sortDirection'     => $request->input('direction', 'desc'),
                ]));
            }
        } catch (\Exception $e) {
            Log::error("Error in TestCaseController@index", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new test case.
     */
    public function create(Project $project, Request $request, ?TestSuite $test_suite = null)
    {
        $this->authorizeAccess($project);

        try {
            // If a test suite is provided, validate it belongs to the project
            if ($test_suite) {
                $this->relationshipValidationService->validateProjectSuiteRelationship($project, $test_suite);
            }

            // Prepare test suites dropdown
            $testSuites = $test_suite
                ? collect([$test_suite])
                : $project->testSuites()->orderBy('name')->get();
            $selectedSuite = $test_suite;

            if (! $test_suite) {
                $selectedSuiteId = $request->input('suite_id');
                $selectedSuite = $testSuites->firstWhere('id', $selectedSuiteId);
            }

            // **Fetch stories for the required story dropdown**
            $storiesForFilter = $project->stories()->orderBy('title')->get();
            $selectedStory = null;
            if ($request->filled('story_id')) {
                $selectedStory = $storiesForFilter->firstWhere('id', $request->input('story_id'));
            }

            return view('dashboard.test-cases.create', [
                'project'          => $project,
                'testSuites'       => $testSuites,
                'selectedSuite'    => $selectedSuite,
                'storiesForFilter' => $storiesForFilter,
                'selectedStory'    => $selectedStory,
                'priorityOptions'  => ['low', 'medium', 'high'],
                'statusOptions'    => ['draft', 'active', 'deprecated', 'archived'],
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Store a newly created test case.
     */
    public function store(TestCaseRequest $request, Project $project, ?TestSuite $test_suite = null)
    {
        $this->authorizeAccess($project);

        // Get validated data
        $validatedData = $request->validated();

        try {
            // Use service to store the test case
            $testCase = $this->testCaseService->store($validatedData, $project, $test_suite);

            // Determine redirect based on context
            if ($test_suite || $request->has('from_suite')) {
                return redirect()->route('dashboard.projects.test-suites.test-cases.index', [
                    $project->id,
                    $test_suite ? $test_suite->id : $request->input('suite_id')
                ])->with('success', 'Test case created successfully.');
            }

            return redirect()->route('dashboard.projects.test-cases.index', $project->id)
                ->with('success', 'Test case created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Error creating test case: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified test case.
     */
    public function show(Project $project, TestCase $test_case, ?TestSuite $test_suite = null)
    {
        $this->authorizeAccess($project);

        try {
            // Get the test case, ensuring it belongs to the project/suite
            $testCase = $this->testCaseService->getTestCase($project, $test_suite, $test_case);

            // Get the suite (either from parameter or from the test case)
            $suite = $test_suite ?? $testCase->testSuite;

            // Get related data
            if ($suite) {
                $relatedCases = TestCase::where('suite_id', $suite->id)
                    ->where('id', '!=', $testCase->id)
                    ->limit(5)
                    ->get();
            } else {
                $relatedCases = collect();
            }
            return view('dashboard.test-cases.show', [
                'project' => $project,
                'testSuite' => $suite,
                'testCase' => $testCase,
                'relatedCases' => $relatedCases
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified test case.
     */
    /**
     * Show the form for editing the specified test case.
     */
    public function edit(Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        try {
            // Eager load relevant relationships to avoid N+1 queries
            $test_case->load(['testSuite', 'story']);

            // Get all test suites for this project for the dropdown
            $testSuites = $project->testSuites()->orderBy('name')->get();

            // Get all stories for this project for the dropdown
            $stories = $project->stories()->orderBy('title')->get();

            return view('dashboard.test-cases.edit', [
                'project' => $project,
                'testCase' => $test_case,
                'testSuites' => $testSuites,
                'stories' => $stories,
                'priorityOptions' => ['low', 'medium', 'high'],
                'statusOptions' => ['draft', 'active', 'deprecated', 'archived']
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading test case edit form: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'test_case_id' => $test_case->id
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update the specified test case.
     */
    public function update(TestCaseRequest $request, Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Get validated data
        $validatedData = $request->validated();

        try {
            // Use service to update the test case, but don't pass the test_suite
            // Instead let the service validate and handle suite/story relationships
            $testCase = $this->testCaseService->update($test_case, $validatedData, $project);

            // After successful update, determine where to redirect
            if ($testCase->suite_id) {
                return redirect()->route('dashboard.projects.test-suites.test-cases.show', [
                    $project->id,
                    $testCase->suite_id,
                    $testCase->id
                ])->with('success', 'Test case updated successfully.');
            }

            return redirect()->route('dashboard.projects.test-cases.show', [
                'project' => $project->id,
                'test_case' => $testCase->id
            ])->with('success', 'Test case updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating test case: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'test_case_id' => $test_case->id,
                'input' => $validatedData
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Error updating test case: ' . $e->getMessage());
        }
    }
    public function getJsonForProject(Project $project, Request $request): JsonResponse
    {
        $suiteId = $request->query('suite_id');

        // you'll need to add this method to StoryService:
        $cases = $this->storyService->getProjectTestCases($project->id, $suiteId);

        return response()->json([
            'success'    => true,
            'test_cases' => $cases,
        ]);
    }


    /**
     * Remove the specified test case.
     */
    public function destroy(Project $project, TestCase $test_case, ?TestSuite $test_suite = null)
    {
        $this->authorizeAccess($project);

        try {
            // Get test case title before deletion
            $testCaseName = $test_case->title;

            // Use service to delete the test case
            $this->testCaseService->destroy($test_case, $project, $test_suite);

            if (request()->expectsJson()) {
                return $this->successResponse([], "Test case \"{$testCaseName}\" deleted successfully.");
            }

            // Determine redirect based on context
            if ($test_suite) {
                return redirect()->route('dashboard.projects.test-suites.test-cases.index', [
                    $project->id,
                    $test_suite->id
                ])->with('success', "Test case \"{$testCaseName}\" deleted successfully.");
            }

            return redirect()->route('dashboard.projects.test-cases.index', $project->id)
                ->with('success', "Test case \"{$testCaseName}\" deleted successfully.");
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return $this->errorResponse('Error deleting test case: ' . $e->getMessage(), 400);
            }

            return redirect()->back()
                ->with('error', 'Error deleting test case: ' . $e->getMessage());
        }
    }

    /**
     * Generate test case details using AI.
     */
    public function generateWithAI(Request $request, Project $project)
    {
        $this->authorizeAccess($project);

        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|min:20|max:2000',
            'suite_id' => 'required|exists:test_suites,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // Add debugging
        Log::info('Starting AI generation', [
            'project_id' => $project->id,
            'suite_id' => $request->input('suite_id'),
            'prompt_length' => strlen($request->input('prompt'))
        ]);

        $apiKey = env('OPENAI_API_KEY', config('services.openai.key'));
        $model = env('OPENAI_MODEL', config('services.openai.model', 'gpt-4o'));

        if (!$apiKey) {
            Log::error('OpenAI API Key is not configured.');
            return $this->errorResponse('AI generation failed.', 500);
        }

        $userPrompt = $request->input('prompt');
        $suiteId = $request->input('suite_id');

        // Get the test suite to provide more context to the AI
        $suite = TestSuite::find($suiteId);
        if (!$suite || $suite->project_id !== $project->id) {
            return $this->errorResponse('Invalid test suite selected.', 422);
        }

        // Construct a detailed prompt for the AI
        $systemPrompt = <<<PROMPT
You are an AI assistant designed to generate detailed test case specifications in JSON format based on user requirements.
You are helping create a test case within the test suite: "{$suite->name}" for project: "{$project->name}".

The user will provide requirements for a feature, functionality, or scenario to test.
Your task is to generate a JSON object containing the following keys:
- "title": A concise, descriptive title for the test case (max 100 chars)
- "description": A more detailed explanation of what the test case verifies (max 255 chars)
- "steps": An array of strings, each representing a single step in the test case procedure
- "expected_results": A detailed description of what should happen when the test is executed correctly
- "priority": One of "low", "medium", or "high" depending on criticality
- "status": "draft" (always use draft for new test cases)
- "tags": An array of relevant tags/keywords for categorization

Example output format:
{
  "title": "Verify User Login with Valid Credentials",
  "description": "Tests that a registered user can log in successfully with valid username and password",
  "steps": [
    "Navigate to login page",
    "Enter valid username",
    "Enter valid password",
    "Click on login button"
  ],
  "expected_results": "User should be logged in successfully and redirected to the dashboard page. The username should be displayed in the header.",
  "priority": "high",
  "status": "draft",
  "tags": ["login", "authentication", "positive-test"]
}

Ensure the output is a valid JSON object with no additional text or explanations.
Make the steps clear, concise, and executable by a human tester.
PROMPT;

        try {
            Log::info('Sending request to OpenAI', [
                'model' => $model,
                'system_prompt_length' => strlen($systemPrompt),
                'user_prompt_length' => strlen($userPrompt)
            ]);

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
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API Error: ' . $response->status() . ' - ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'AI generation failed. Error: ' . $response->status() . ' - ' . $response->body()
                ], 500);
            }

            $aiContent = $response->json('choices.0.message.content');
            Log::info('Received response from OpenAI', ['content_length' => strlen($aiContent)]);

            // Clean and parse the JSON
            $jsonString = trim(str_replace(['```json', '```'], '', $aiContent));
            $generatedData = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($generatedData)) {
                Log::error('AI returned invalid JSON: ' . $jsonString);
                return response()->json([
                    'success' => false,
                    'message' => 'AI returned an invalid format. Please try again or refine your prompt.'
                ], 500);
            }

            // Basic validation of the returned structure
            if (!isset($generatedData['title']) || !isset($generatedData['steps']) || !isset($generatedData['expected_results'])) {
                Log::error('AI JSON missing required keys: ' . json_encode($generatedData));
                return response()->json([
                    'success' => false,
                    'message' => 'AI response missing required fields. Please try again.'
                ], 500);
            }

            // Ensure steps is an array
            if (!is_array($generatedData['steps'])) {
                $generatedData['steps'] = [];
            }

            // Ensure tags is an array
            if (!isset($generatedData['tags']) || !is_array($generatedData['tags'])) {
                $generatedData['tags'] = [];
            }

            // Set priority if not set or invalid
            if (!isset($generatedData['priority']) || !in_array($generatedData['priority'], ['low', 'medium', 'high'])) {
                $generatedData['priority'] = 'medium';
            }

            // Always set status to draft
            $generatedData['status'] = 'draft';

            Log::info('Successfully generated test case with AI', [
                'title' => $generatedData['title'],
                'steps_count' => count($generatedData['steps']),
                'tags_count' => count($generatedData['tags'])
            ]);

            return response()->json([
                'success' => true,
                'data' => $generatedData
            ]);
        } catch (\Exception $e) {
            Log::error('Error calling OpenAI API: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('An unexpected error occurred during AI generation: ' . $e->getMessage(), 500);
        }
    }
}
