<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TestSuite;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreTestSuiteRequest;
use App\Http\Requests\UpdateTestSuiteRequest;
use App\Services\AI\AIGenerationService;
use App\Services\RelationshipValidationService;
use App\Services\TestCaseService;
use App\Services\TestSuiteService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Needed for indexAll safety check
use App\Traits\JsonResponse;
use App\Traits\AuthorizeResourceAccess;


class TestSuiteController extends Controller
{
    use AuthorizeResourceAccess, JsonResponse;

    protected TestSuiteService $suites;
    protected RelationshipValidationService $relationshipValidator;
    protected TestCaseService $testCaseService;

    public function __construct(TestSuiteService $suites, RelationshipValidationService $relationshipValidator, TestCaseService $testCaseService)
    {
        $this->suites = $suites;
        $this->relationshipValidator = $relationshipValidator;
        $this->testCaseService = $testCaseService;
    }

    /**
     * GET /dashboard/api/projects/{project}/test-suites
     */
    public function getJsonForProject(Project $project)
    {
        $suites = $project
            ->testSuites()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);

        return response()->json([
            'success'     => true,
            'test_suites' => $suites,
        ]);
    }

    // --- indexAll ---
    public function indexAll(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        // Get project IDs the current user actually belongs to within this team
        $userProjectIds = Auth::user()->teams()
            ->where('teams.id', $currentTeamId)
            ->first()?->projects()->pluck('id'); // Use optional chaining

        $projectsForFilter = Project::whereIn('id', $userProjectIds)->orderBy('name')->get(['id', 'name']);

        $query = TestSuite::query()
            ->whereIn('project_id', $userProjectIds)
            ->with(['project:id,name'])->withCount('testCases') // Removed testCases load here for performance
            ->orderBy('updated_at', 'desc');

        $filterProjectId = $request->input('project_id');
        if ($filterProjectId && $projectsForFilter->contains('id', $filterProjectId)) {
            $query->where('project_id', $filterProjectId);
        }

        $testSuites = $query->get();

        return view('dashboard.test-suites.index', [
            'testSuites' => $testSuites,
            'projects' => $projectsForFilter,
            'team' => $team
        ]);
    }

    // --- index ---
    public function index(Project $project)
    {
        $this->authorizeAccess($project);
        $testSuites = $project->testSuites()
            ->withCount('testCases')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dashboard.test-suites.index', compact('project', 'testSuites'));
    }

    // --- create ---
    public function create(Project $project)
    {
        $this->authorizeAccess($project);
        return view('dashboard.test-suites.create', compact('project'));
    }

    // --- store ---
    public function store(StoreTestSuiteRequest $request, Project $project)
    {
        $this->authorizeAccess($project);
        $suite = $this->suites->create($project, $request->validated());

        return redirect()
            ->route('dashboard.projects.test-suites.index', $project->id)
            ->with('success', 'Test Suite "' . $suite->name . '" created.');
    }

    // --- show ---
    public function show(Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);
        $test_suite->loadMissing('testCases');

        return view('dashboard.test-suites.show', [
            'project'   => $project,
            'testSuite' => $test_suite,
        ]);
    }

    // --- edit ---
    public function edit(Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);
        return view('dashboard.test-suites.edit', [
            'project'   => $project,
            'testSuite' => $test_suite,
        ]);
    }

    // --- update ---
    public function update(UpdateTestSuiteRequest $request, Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);
        $suite = $this->suites->update($test_suite, $request->validated());

        return redirect()
            ->route('dashboard.projects.test-suites.show', [$project->id, $suite->id])
            ->with('success', 'Test Suite updated.');
    }

    // --- destroy ---
    public function destroy(Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);
        $name = $test_suite->name;
        $this->suites->delete($test_suite);

        if (request()->expectsJson()) {
            return $this->successResponse([], "Suite \"$name\" deleted.");
        }

        return redirect()
            ->route('dashboard.projects.test-suites.index', $project->id)
            ->with('success', "Suite \"$name\" deleted.");
    }

    /**
     * Generate Test Suite details using AI.
     */
    public function generateWithAI(Request $request, Project $project)
    {
        $this->authorizeAccess($project);

        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|min:20|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Set up context with project info
            $context = [
                'project_id' => $project->id,
                'project_name' => $project->name
            ];

            // Generate the test suite
            $aiService = app(AIGenerationService::class);
            $testSuite = $aiService->generateTestSuite($request->input('prompt'), $context);

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $testSuite->name,
                    'description' => $testSuite->description,
                    'settings' => $testSuite->settings
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating test suite with AI: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during AI generation.'
            ], 500);
        }
    }

    /**
     * Search for test cases that can be added to this test suite.
     */
    public function searchAvailableTestCases(Request $request, Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);

        // Validate relationship
        try {
            $this->relationshipValidator->validateProjectSuiteRelationship($project, $test_suite);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }

        // Validate search parameters
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:5|max:50',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $result = $this->testCaseService->searchAvailableTestCases(
                $project,
                $test_suite,
                $request->only(['search', 'per_page', 'page'])
            );

            return $this->successResponse($result);
        } catch (\Exception $e) {
            Log::error('Error searching available test cases: ' . $e->getMessage());
            return $this->errorResponse('Failed to search test cases: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add existing test cases to a test suite.
     */
    public function addTestCases(Request $request, Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);

        // Validate relationship
        try {
            $this->relationshipValidator->validateProjectSuiteRelationship($project, $test_suite);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'test_case_ids' => 'required|array',
            'test_case_ids.*' => 'required|uuid|exists:test_cases,id',
        ]);

        if ($validator->fails()) {
            Log::error('Test case addition validation failed: ' . json_encode($validator->errors()->toArray()));
            return $this->validationErrorResponse($validator);
        }

        try {
            $result = $this->suites->addTestCasesToSuite(
                $test_suite,
                $request->input('test_case_ids')
            );

            return $this->successResponse($result, $result['message']);
        } catch (\Exception $e) {
            Log::error('Error adding test cases to suite: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'test_suite_id' => $test_suite->id,
                'test_case_ids' => $request->input('test_case_ids')
            ]);
            return $this->errorResponse('Failed to add test cases: ' . $e->getMessage(), 500);
        }
    }
}
