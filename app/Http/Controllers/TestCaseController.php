<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestCaseRequest;
use App\Http\Requests\TestCaseIndexRequest;
use App\Models\Project;
use App\Models\Team;
use App\Models\TestCase;
use App\Models\TestSuite;
use App\Services\AI\AIGenerationService;
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

        // Get all filter parameters
        $filters = [
            'suite_id'  => $request->input('suite_id'),
            'story_id'  => $request->input('story_id'),
            'search'    => $request->input('search'),
            'sort'      => $request->input('sort', 'updated_at'),
            'direction' => $request->input('direction', 'desc'),
        ];

        try {
            // Get test cases with filters applied
            if ($test_suite) {
                // Suite-specific listing
                $data = $this->testCaseService->getTestCasesForSuite(
                    $project,
                    $test_suite,
                    $filters
                );
            } else {
                // Project-wide listing
                $data = $this->testCaseService->getTestCasesForProject(
                    $project,
                    $filters
                );
            }

            // Return view with all necessary data
            return view('dashboard.test-cases.index', array_merge($data, [
                'project'           => $project,
                'testSuite'         => $test_suite,
                'storiesForFilter'  => $storiesForFilter,
                'suitesForFilter'   => $suitesForFilter,
                'selectedStoryId'   => $filters['story_id'],
                'selectedSuiteId'   => $filters['suite_id'],
                'searchTerm'        => $filters['search'],
                'sortField'         => $filters['sort'],
                'sortDirection'     => $filters['direction'],
            ]));
        } catch (\Exception $e) {
            Log::error("Error in TestCaseController@index", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a test case from a test suite.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project $project
     * @param \App\Models\TestCase $test_case
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromSuite(Request $request, Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        try {
            // Validate that the test case belongs to a suite in this project
            $suite = $test_case->testSuite;

            if (!$suite) {
                return $this->errorResponse('This test case is not associated with any test suite.', 400);
            }

            if ($suite->project_id !== $project->id) {
                return $this->errorResponse('Test case not found in this project.', 404);
            }

            // Optional: Validate a specific suite ID if provided in the request
            if ($request->has('suite_id') && $suite->id !== $request->input('suite_id')) {
                return $this->errorResponse('Test case is not in the specified suite.', 400);
            }

            // Save the original data for response
            $caseName = $test_case->title;
            $suiteName = $suite->name;

            // Set the suite_id to null to remove from suite (doesn't delete the test case)
            $test_case->suite_id = null;
            $test_case->save();

            // Log the action for auditing purposes
            Log::info('Test case removed from suite', [
                'test_case_id' => $test_case->id,
                'test_case_name' => $caseName,
                'suite_id' => $suite->id,
                'suite_name' => $suiteName,
                'user_id' => Auth::id()
            ]);

            return $this->successResponse([
                'test_case_id' => $test_case->id,
                'test_case_name' => $caseName,
                'suite_id' => $suite->id,
                'suite_name' => $suiteName
            ], "Test case \"$caseName\" removed from \"$suiteName\" suite.");
        } catch (\Exception $e) {
            Log::error('Failed to remove test case from suite: ' . $e->getMessage(), [
                'test_case_id' => $test_case->id,
                'project_id' => $project->id
            ]);

            return $this->errorResponse('Failed to remove test case from suite: ' . $e->getMessage(), 500);
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

        // Get the test suite for context
        $suite = TestSuite::find($request->input('suite_id'));
        if (!$suite || $suite->project_id !== $project->id) {
            return $this->errorResponse('Invalid test suite selected.', 422);
        }

        try {
            // Set up context with project and suite info
            $context = [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'suite_id' => $suite->id,
                'suite_name' => $suite->name
            ];

            // Generate the test case
            $aiService = app(AIGenerationService::class);
            $testCase = $aiService->generateTestCase($request->input('prompt'), $context);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $testCase->id,
                    'title' => $testCase->title,
                    'description' => $testCase->description,
                    'steps' => $testCase->steps,
                    'expected_results' => $testCase->expected_results,
                    'priority' => $testCase->priority,
                    'status' => $testCase->status,
                    'tags' => $testCase->tags,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating test case with AI: ' . $e->getMessage());
            return $this->errorResponse('An unexpected error occurred during AI generation: ' . $e->getMessage(), 500);
        }
    }
}
