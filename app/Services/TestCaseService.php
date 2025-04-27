<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Story;
use App\Models\Team;
use App\Models\TestCase;
use App\Models\TestSuite;
use App\Services\RelationshipValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TestCaseService
{
    protected $relationshipValidator;

    public function __construct(RelationshipValidationService $relationshipValidator)
    {
        $this->relationshipValidator = $relationshipValidator;
    }
    /**
     * Get all test cases for a team with optional filtering
     */
    public function getAllTestCasesForTeam($teamId, $filters = [])
    {
        $team = Team::find($teamId);
        if (!$team) {
            Log::warning('TestCase indexAll access failed: Team not found', [
                'team_id' => $teamId,
                'user_id' => Auth::id()
            ]);
            throw new \Exception('Invalid team selection. Please re-select.');
        }

        // Get project IDs the current user belongs to within this team
        $userProjectIds = Auth::user()->teams()
            ->where('teams.id', $teamId)
            ->first()?->projects()
            ->pluck('id');

        if (is_null($userProjectIds) || $userProjectIds->isEmpty()) {
            return [
                'testCases' => collect(),
                'projects' => collect(),
                'testSuites' => collect(),
                'team' => $team
            ];
        }

        // Get projects for filter dropdown
        $projects = \App\Models\Project::whereIn('id', $userProjectIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Extract filters
        $selectedProjectId = $filters['project_id'] ?? null;
        $selectedSuiteId = $filters['suite_id'] ?? null;
        $search = $filters['search'] ?? null;
        $sortField = $filters['sort'] ?? 'updated_at';
        $sortDirection = $filters['direction'] ?? 'desc';
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
                'projects.id as project_id'
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
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('test_cases.title', 'like', $searchTerm)
                    ->orWhere('test_cases.expected_results', 'like', $searchTerm);
            });
        }

        // Sort options
        $allowedSortFields = ['title', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy("test_cases.{$sortField}", $sortDirection);
        } else {
            $query->orderBy('test_cases.updated_at', 'desc');
        }

        // Get paginated results
        $testCases = $query->paginate(10)->withQueryString();

        return [
            'testCases' => $testCases,
            'projects' => $projects,
            'testSuites' => $testSuites,
            'team' => $team
        ];
    }

    /**
     * Get filtered test cases for the index view, along with filter data.
     *
     * @param Team $team The current team context.
     * @param array $filters Validated filter parameters from TestCaseIndexRequest.
     * @return array Data for the view.
     */
    public function getFilteredTestCasesForTeam(Team $team, array $filters): array
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('User not authenticated.');
        }

        // 1. Fetch data for filters, scoped by team/project selections
        // Projects: All projects the user has access to within the current team
        $projectsForFilter = $team->projects()
            ->orderBy('name')
            ->get(['id', 'name']);

        // Stories: Only fetch if a project is selected
        $storiesForFilter = collect();
        if (!empty($filters['project_id'])) {
            $storiesForFilter = Story::where('project_id', $filters['project_id'])
                ->orderBy('title')
                ->get(['id', 'title']);
        }

        // Suites: Only fetch if a project is selected (we'll filter further based on story later if needed)
        $suitesForFilter = collect();
        if (!empty($filters['project_id'])) {
            $suitesForFilter = TestSuite::where('project_id', $filters['project_id'])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        // 2. Build the Test Case Query
        $query = TestCase::query()
            // Select specific columns for efficiency and clarity
            ->select([
                'test_cases.id',
                'test_cases.title',
                'test_cases.priority',
                'test_cases.status',
                'test_cases.updated_at',
                'test_cases.suite_id', // Needed for display/linking
                'test_cases.story_id', // Needed for filtering
                'test_suites.name as suite_name',
                'stories.title as story_title',
                'projects.id as project_id',
                'projects.name as project_name'
            ])
            // Join related tables needed for filtering and display
            ->join('test_suites', 'test_cases.suite_id', '=', 'test_suites.id')
            ->join('projects', 'test_suites.project_id', '=', 'projects.id')
            ->leftJoin('stories', 'test_cases.story_id', '=', 'stories.id') // Left join in case story isn't mandatory? Check model. Migration says NOT NULL, so use INNER JOIN.
            // ->join('stories', 'test_cases.story_id', '=', 'stories.id') // Use this if story_id is required
            ->where('projects.team_id', $team->id); // Filter by the current team


        // 3. Apply Filters
        if (!empty($filters['project_id'])) {
            $query->where('projects.id', $filters['project_id']);
        }
        if (!empty($filters['story_id'])) {
            $query->where('test_cases.story_id', $filters['story_id']);
        }
        if (!empty($filters['suite_id'])) {
            $query->where('test_cases.suite_id', $filters['suite_id']);
        }
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('test_cases.title', 'like', $searchTerm)
                  ->orWhere('test_cases.description', 'like', $searchTerm); // Assuming description exists
            });
        }

        // 4. Apply Sorting
        $sortField = $filters['sort'] ?? 'test_cases.updated_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        // Validate sort field to prevent SQL injection
        $allowedSortFields = [
            'title' => 'test_cases.title',
            'updated_at' => 'test_cases.updated_at',
            'created_at' => 'test_cases.created_at',
            'priority' => 'test_cases.priority',
            'status' => 'test_cases.status',
            'suite_name' => 'test_suites.name', // Example for sorting by related field
            'project_name' => 'projects.name'   // Example
        ];

        if (array_key_exists($sortField, $allowedSortFields)) {
            $query->orderBy($allowedSortFields[$sortField], $sortDirection);
        } else {
            $query->orderBy('test_cases.updated_at', 'desc'); // Default sort
        }

        // 5. Paginate Results
        $testCases = $query->paginate(15)->withQueryString(); // Adjust page size as needed

        // 6. Return data for the view
        return [
            'testCases' => $testCases,
            'projectsForFilter' => $projectsForFilter,
            'storiesForFilter' => $storiesForFilter,
            'suitesForFilter' => $suitesForFilter,
            'team' => $team,
            'filters' => $filters // Pass applied filters back to view for persistence
        ];
    }

    /**
     * Get test cases for a specific project with optional filtering
     */
    public function getTestCasesForProject(Project $project, $filters = [])
    {
        // Extract filters
        $selectedSuiteId = $filters['suite_id'] ?? null;
        $search = $filters['search'] ?? null;
        $sortField = $filters['sort'] ?? 'updated_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        $query = TestCase::query()
        ->with(['story', 'testSuite'])
        // optional suite filter
        ->when(isset($filters['suite_id']), fn($q,$suiteId) => $q->where('suite_id', $suiteId))
        // include both suite-linked & suite-less cases via story
        ->where(function($q) use ($project) {
            $q->whereHas('testSuite', fn($q2) =>
                    $q2->where('project_id', $project->id)
                )
              ->orWhereDoesntHave('testSuite')
              ->whereHas('story', fn($q2) =>
                    $q2->where('project_id', $project->id)
                );
        });

        if ($selectedSuiteId) {
            $testSuites = $project->testSuites()->orderBy('name')->get(['id', 'name']);
            if ($testSuites->contains('id', $selectedSuiteId)) {
                $query->where('test_suites.id', $selectedSuiteId);
            }
        }

        // Add search functionality
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('test_cases.title', 'like', $searchTerm)
                    ->orWhere('test_cases.expected_results', 'like', $searchTerm);
            });
        }

        // Sorting
        $allowedSortFields = ['title', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy("test_cases.{$sortField}", $sortDirection);
        } else {
            $query->orderBy('test_cases.updated_at', 'desc');
        }

        $testCases = $query->paginate(10)->withQueryString();
        $testSuites = $project->testSuites()->orderBy('name')->get(['id', 'name']);

        return [
            'testCases'  => $query
                ->orderBy($filters['sort'] ?? 'updated_at', $filters['direction'] ?? 'desc')
                ->paginate(20),
            'testSuites' => $project->testSuites()->orderBy('name')->get(),
        ];
    }

    /**
     * Get test cases for a specific test suite
     */
    public function getTestCasesForSuite(Project $project, TestSuite $testSuite, $filters = [])
    {
        // Validate project-suite relationship
        $this->relationshipValidator->validateProjectSuiteRelationship($project, $testSuite);

        // Extract filters
        $search = $filters['search'] ?? null;
        $sortField = $filters['sort'] ?? 'updated_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        $query = TestCase::where('suite_id', $testSuite->id);

        // Add search functionality
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('expected_results', 'like', $searchTerm);
            });
        }

        // Sorting
        $allowedSortFields = ['title', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $testCases = $query->paginate(10)->withQueryString();

        return [
            'testCases' => $testCases
        ];
    }

    /**
     * Get a test case, ensuring it belongs to the project and optionally to a specific suite.
     */
    public function getTestCase(Project $project, ?TestSuite $testSuite, TestCase $testCase): TestCase
    {
        // First validate the project-suite relationship
        if ($testSuite) {
            $this->relationshipValidator->validateProjectSuiteRelationship($project, $testSuite);

            // Ensure test case belongs to the specified suite
            if ($testCase->suite_id !== $testSuite->id) {
                throw new \Exception('Test case not found in this test suite.');
            }
        } else {
            // Ensure test case belongs to a suite in this project
            $suite = $testCase->testSuite;
            if (!$suite || $suite->project_id !== $project->id) {
                throw new \Exception('Test case not found in this project.');
            }
        }

        return $testCase;
    }

    /**
     * Store a new test case.
     */
    public function store(array $data, Project $project, ?TestSuite $testSuite = null): TestCase
    {
        // If a test suite is provided, validate it belongs to the project
        if ($testSuite) {
            $this->relationshipValidator->validateProjectSuiteRelationship($project, $testSuite);
            // Ensure suite_id is set in the data
            $data['suite_id'] = $testSuite->id;
        }

        // Create the test case
        $testCase = new TestCase();
        $testCase->fill($data);
        $testCase->save();

        return $testCase;
    }

    /**
     * Update a test case.
     */
    public function update(TestCase $testCase, array $data, Project $project, ?TestSuite $testSuite = null): TestCase
    {
        // Get the test case, ensuring it belongs to the project/suite
        $testCase = $this->getTestCase($project, $testSuite, $testCase);

        // Update the test case
        $testCase->fill($data);
        $testCase->save();

        return $testCase;
    }

    /**
     * Delete a test case.
     */
    public function destroy(TestCase $testCase, Project $project, ?TestSuite $testSuite = null): ?bool
    {
        // Get the test case, ensuring it belongs to the project/suite
        $testCase = $this->getTestCase($project, $testSuite, $testCase);

        // Delete the test case
        return $testCase->delete();
    }
}
