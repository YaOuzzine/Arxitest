<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestSuite;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TestCaseController extends Controller
{
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
    public function indexAll(Request $request)
    {
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Please select a team first.');
        }

        $team = Team::find($currentTeamId);
        if (!$team) {
            Log::warning(
                'TestCase indexAll access failed: Session team invalid.',
                ['user_id' => Auth::id(), 'session_current_team' => $currentTeamId]
            );
            session()->forget('current_team');
            return redirect()->route('dashboard.select-team')->with('error', 'Invalid team selection. Please re-select.');
        }

        // Get project IDs the current user belongs to within this team
        $userProjectIds = Auth::user()->teams()
            ->where('teams.id', $currentTeamId)
            ->first()?->projects()
            ->pluck('id');

        if (is_null($userProjectIds) || $userProjectIds->isEmpty()) {
            return view('dashboard.test-cases.index', [
                'testCases' => collect(),
                'projects' => collect(),
                'testSuites' => collect(),
                'team' => $team,
                'selectedProjectId' => null,
                'selectedSuiteId' => null
            ]);
        }

        // Get projects for filter dropdown
        $projects = Project::whereIn('id', $userProjectIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Handle project filter
        $selectedProjectId = $request->input('project_id');
        $selectedSuiteId = $request->input('suite_id');
        $testSuites = collect();

        // Base query for test cases
        $query = TestCase::query()
            ->join('test_suites', 'test_cases.suite_id', '=', 'test_suites.id')
            ->join('projects', 'test_suites.project_id', '=', 'projects.id')
            ->whereIn('projects.id', $userProjectIds)
            ->select(
                'test_cases.*',
                'test_suites.name as suite_name',
                'projects.name as project_name',
                'projects.id as project_id'  // Make sure project_id is explicitly selected
            );

        // Apply filters if provided
        if ($selectedProjectId && $projects->contains('id', $selectedProjectId)) {
            $query->where('projects.id', $selectedProjectId);

            // Get test suites for the selected project
            $testSuites = TestSuite::where('project_id', $selectedProjectId)
                ->orderBy('name')
                ->get(['id', 'name']);

            if ($selectedSuiteId && $testSuites->contains('id', $selectedSuiteId)) {
                $query->where('test_cases.suite_id', $selectedSuiteId);
            }
        }

        // Add search query if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('test_cases.title', 'like', $searchTerm)
                    ->orWhere('test_cases.expected_results', 'like', $searchTerm);
            });
        }

        // Sort options
        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSortFields = ['title', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy("test_cases.{$sortField}", $sortDirection);
        } else {
            $query->orderBy('test_cases.updated_at', 'desc');
        }

        // Get paginated results
        $testCases = $query->paginate(10)->withQueryString();

        return view('dashboard.test-cases.index', [
            'testCases' => $testCases,
            'projects' => $projects,
            'testSuites' => $testSuites,
            'team' => $team,
            'selectedProjectId' => $selectedProjectId,
            'selectedSuiteId' => $selectedSuiteId,
            'searchTerm' => $request->search ?? '',
            'sortField' => $sortField,
            'sortDirection' => $sortDirection
        ]);
    }

    /**
     * Display a listing of the test cases for a specific project.
     */
    public function index(Project $project, Request $request)
    {
        $this->authorizeAccess($project);

        $testSuites = $project->testSuites()->orderBy('name')->get(['id', 'name']);
        $selectedSuiteId = $request->input('suite_id');

        $query = TestCase::query()
            ->join('test_suites', 'test_cases.suite_id', '=', 'test_suites.id')
            ->where('test_suites.project_id', $project->id)
            ->select('test_cases.*', 'test_suites.name as suite_name');

        if ($selectedSuiteId && $testSuites->contains('id', $selectedSuiteId)) {
            $query->where('test_suites.suite_id', $selectedSuiteId);
        }

        // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('test_cases.title', 'like', $searchTerm)
                    ->orWhere('test_cases.expected_results', 'like', $searchTerm);
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSortFields = ['title', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy("test_cases.{$sortField}", $sortDirection);
        } else {
            $query->orderBy('test_cases.updated_at', 'desc');
        }

        $testCases = $query->paginate(10)->withQueryString();

        return view('dashboard.test-cases.index', [
            'testCases' => $testCases,
            'project' => $project,
            'testSuites' => $testSuites,
            'selectedSuiteId' => $selectedSuiteId,
            'searchTerm' => $request->search ?? '',
            'sortField' => $sortField,
            'sortDirection' => $sortDirection
        ]);
    }

    /**
     * Display a listing of the test cases for a specific test suite.
     */
    public function indexBySuite(Project $project, TestSuite $test_suite, Request $request)
    {
        $this->authorizeAccess($project);

        // Ensure the test suite belongs to the project
        if ($test_suite->project_id !== $project->id) {
            abort(404, 'Test suite not found in this project.');
        }

        $query = TestCase::where('suite_id', $test_suite->id);

        // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('expected_results', 'like', $searchTerm);
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSortFields = ['title', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $testCases = $query->paginate(10)->withQueryString();

        return view('dashboard.test-cases.index', [
            'testCases' => $testCases,
            'project' => $project,
            'testSuite' => $test_suite,
            'searchTerm' => $request->search ?? '',
            'sortField' => $sortField,
            'sortDirection' => $sortDirection
        ]);
    }

    /**
     * Show the form for creating a new test case.
     */
    public function create(Project $project, Request $request)
    {
        $this->authorizeAccess($project);

        $testSuites = $project->testSuites()->orderBy('name')->get();
        $selectedSuiteId = $request->input('suite_id');

        $suite = null;
        if ($selectedSuiteId && $testSuites->contains('id', $selectedSuiteId)) {
            $suite = $testSuites->firstWhere('id', $selectedSuiteId);
        }

        return view('dashboard.test-cases.create', [
            'project' => $project,
            'testSuites' => $testSuites,
            'selectedSuite' => $suite,
            'priorityOptions' => ['low', 'medium', 'high'],
            'statusOptions' => ['draft', 'active', 'deprecated', 'archived']
        ]);
    }

    /**
     * Show the form for creating a new test case for a specific suite.
     */
    public function createForSuite(Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);

        // Ensure the test suite belongs to the project
        if ($test_suite->project_id !== $project->id) {
            abort(404, 'Test suite not found in this project.');
        }

        return view('dashboard.test-cases.create', [
            'project' => $project,
            'testSuites' => collect([$test_suite]),
            'selectedSuite' => $test_suite,
            'priorityOptions' => ['low', 'medium', 'high'],
            'statusOptions' => ['draft', 'active', 'deprecated', 'archived']
        ]);
    }

    /**
     * Generate test case details using AI.
     */
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
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
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
            return response()->json([
                'success' => false,
                'message' => 'AI service is not configured. Please check your API key in .env file.'
            ], 500);
        }

        $userPrompt = $request->input('prompt');
        $suiteId = $request->input('suite_id');

        // Get the test suite to provide more context to the AI
        $suite = TestSuite::find($suiteId);
        if (!$suite || $suite->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid test suite selected.'
            ], 422);
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
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during AI generation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created test case.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorizeAccess($project);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'expected_results' => 'required|string',
            'steps' => 'required|array|min:1',
            'steps.*' => 'required|string|max:500',
            'suite_id' => [
                'required',
                function ($attribute, $value, $fail) use ($project) {
                    $suite = TestSuite::find($value);
                    if (!$suite || $suite->project_id !== $project->id) {
                        $fail('The selected test suite is invalid.');
                    }
                }
            ],
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:draft,active,deprecated,archived',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create the test case
        $testCase = new TestCase();
        $testCase->title = $request->input('title');
        $testCase->description = $request->input('description');
        $testCase->expected_results = $request->input('expected_results');
        $testCase->steps = json_encode($request->input('steps')); // JSON encode the steps array
        $testCase->suite_id = $request->input('suite_id');
        $testCase->priority = $request->input('priority');
        $testCase->status = $request->input('status');
        $testCase->tags = json_encode($request->input('tags', [])); // JSON encode the tags array
        $testCase->save();

        // Determine where to redirect based on context
        if ($request->has('from_suite')) {
            return redirect()->route('dashboard.projects.test-suites.test-cases.index', [
                $project->id,
                $request->input('suite_id')
            ])->with('success', 'Test case created successfully.');
        }

        return redirect()->route('dashboard.projects.test-cases.index', $project->id)
            ->with('success', 'Test case created successfully.');
    }

    /**
     * Store a test case for a specific suite.
     */
    public function storeForSuite(Request $request, Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);

        // Ensure the suite belongs to the project
        if ($test_suite->project_id !== $project->id) {
            abort(404, 'Test suite not found in this project.');
        }

        // Set the suite_id in the request
        $request->merge(['suite_id' => $test_suite->id]);

        return $this->store($request, $project);
    }

    /**
     * Display the specified test case.
     */
    public function show(Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            abort(404, 'Test case not found in this project.');
        }

        // Get related data
        $relatedCases = TestCase::where('suite_id', $suite->id)
            ->where('id', '!=', $test_case->id)
            ->limit(5)
            ->get();

        return view('dashboard.test-cases.show', [
            'project' => $project,
            'testSuite' => $suite,
            'testCase' => $test_case,
            'relatedCases' => $relatedCases
        ]);
    }

    /**
     * Display the specified test case within a suite context.
     */
    public function showForSuite(Project $project, TestSuite $test_suite, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test suite belongs to project
        if ($test_suite->project_id !== $project->id) {
            abort(404, 'Test suite not found in this project.');
        }

        // Ensure test case belongs to the suite
        if ($test_case->suite_id !== $test_suite->id) {
            abort(404, 'Test case not found in this test suite.');
        }

        // Get related data
        $relatedCases = TestCase::where('suite_id', $test_suite->id)
            ->where('id', '!=', $test_case->id)
            ->limit(5)
            ->get();

        return view('dashboard.test-cases.show', [
            'project' => $project,
            'testSuite' => $test_suite,
            'testCase' => $test_case,
            'relatedCases' => $relatedCases
        ]);
    }

    /**
     * Show the form for editing the specified test case.
     */
    public function edit(Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            abort(404, 'Test case not found in this project.');
        }

        $testSuites = $project->testSuites()->orderBy('name')->get();

        return view('dashboard.test-cases.edit', [
            'project' => $project,
            'testSuites' => $testSuites,
            'testCase' => $test_case,
            'selectedSuite' => $suite,
            'priorityOptions' => ['low', 'medium', 'high'],
            'statusOptions' => ['draft', 'active', 'deprecated', 'archived']
        ]);
    }

    /**
     * Update the specified test case.
     */
    public function update(Request $request, Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            abort(404, 'Test case not found in this project.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'expected_results' => 'required|string',
            'steps' => 'required|array|min:1',
            'steps.*' => 'required|string|max:500',
            'suite_id' => [
                'required',
                function ($attribute, $value, $fail) use ($project) {
                    $suite = TestSuite::find($value);
                    if (!$suite || $suite->project_id !== $project->id) {
                        $fail('The selected test suite is invalid.');
                    }
                }
            ],
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:draft,active,deprecated,archived',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update the test case
        $test_case->title = $request->input('title');
        $test_case->description = $request->input('description');
        $test_case->expected_results = $request->input('expected_results');
        $test_case->steps = $request->input('steps');
        $test_case->suite_id = $request->input('suite_id');
        $test_case->priority = $request->input('priority');
        $test_case->status = $request->input('status');
        $test_case->tags = $request->input('tags', []);
        $test_case->save();

        // Determine where to redirect based on context
        if ($request->input('from_suite')) {
            return redirect()->route('dashboard.projects.test-suites.test-cases.index', [
                $project->id,
                $test_case->suite_id
            ])->with('success', 'Test case updated successfully.');
        }

        return redirect()->route('dashboard.projects.test-cases.index', $project->id)
            ->with('success', 'Test case updated successfully.');
    }

    /**
     * Update a test case within a suite context.
     */
    public function updateForSuite(Request $request, Project $project, TestSuite $test_suite, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test suite belongs to project
        if ($test_suite->project_id !== $project->id) {
            abort(404, 'Test suite not found in this project.');
        }

        // Ensure test case belongs to the suite
        if ($test_case->suite_id !== $test_suite->id) {
            abort(404, 'Test case not found in this test suite.');
        }

        // Pass to main update method with suite context
        $request->merge(['from_suite' => true]);
        return $this->update($request, $project, $test_case);
    }

    /**
     * Remove the specified test case.
     */
    public function destroy(Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            abort(404, 'Test case not found in this project.');
        }

        $testCaseName = $test_case->title;
        $suiteId = $test_case->suite_id;

        // Delete the test case
        $test_case->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Test case \"$testCaseName\" deleted successfully."
            ]);
        }

        return redirect()->route('dashboard.projects.test-cases.index', $project->id)
            ->with('success', "Test case \"$testCaseName\" deleted successfully.");
    }

    /**
     * Remove a test case within a suite context.
     */
    public function destroyForSuite(Project $project, TestSuite $test_suite, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test suite belongs to project
        if ($test_suite->project_id !== $project->id) {
            abort(404, 'Test suite not found in this project.');
        }

        // Ensure test case belongs to the suite
        if ($test_case->suite_id !== $test_suite->id) {
            abort(404, 'Test case not found in this test suite.');
        }

        $testCaseName = $test_case->title;

        // Delete the test case
        $test_case->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Test case \"$testCaseName\" deleted successfully."
            ]);
        }

        return redirect()->route('dashboard.projects.test-suites.test-cases.index', [
            $project->id,
            $test_suite->id
        ])->with('success', "Test case \"$testCaseName\" deleted successfully.");
    }
}
