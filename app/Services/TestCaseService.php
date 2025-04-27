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
use Illuminate\Support\Facades\DB;

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

        // 1. Get projects for this team
        $projectsForFilter = $team->projects()
            ->orderBy('name')
            ->get(['id', 'name']);

        $projectIds = $projectsForFilter->pluck('id')->toArray();

        // If no projects, return empty data
        if (empty($projectIds)) {
            return [
                'testCases' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'projectsForFilter' => $projectsForFilter,
                'storiesForFilter' => collect(),
                'suitesForFilter' => collect(),
                'team' => $team,
                'filters' => $filters
            ];
        }

        // 2. Stories and suites for filtering UI
        $storiesForFilter = collect();
        $suitesForFilter = collect();
        if (!empty($filters['project_id'])) {
            $storiesForFilter = Story::where('project_id', $filters['project_id'])
                ->orderBy('title')
                ->get(['id', 'title']);

            $suitesForFilter = TestSuite::where('project_id', $filters['project_id'])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        // 3. Base query - use a simple approach with query builder
        $query = TestCase::query();

        // 4. First, limit to test cases associated with this team's projects
        $query->where(function ($q) use ($projectIds) {
            // Test cases linked via test suites
            $q->whereExists(function ($subq) use ($projectIds) {
                $subq->select(DB::raw(1))
                    ->from('test_suites')
                    ->whereRaw('test_cases.suite_id = test_suites.id')
                    ->whereIn('test_suites.project_id', $projectIds);
            });

            // OR test cases linked via stories
            $q->orWhereExists(function ($subq) use ($projectIds) {
                $subq->select(DB::raw(1))
                    ->from('stories')
                    ->whereRaw('test_cases.story_id = stories.id')
                    ->whereIn('stories.project_id', $projectIds);
            });
        });

        // Select needed columns with proper aliases
        $query->select([
            'test_cases.id',
            'test_cases.title',
            'test_cases.description',
            'test_cases.expected_results',
            'test_cases.priority',
            'test_cases.status',
            'test_cases.created_at',
            'test_cases.updated_at',
            'test_cases.suite_id',
            'test_cases.story_id',
        ]);

        // 5. Apply filters
        if (!empty($filters['project_id'])) {
            $projectId = $filters['project_id'];
            $query->where(function ($q) use ($projectId) {
                // Test cases linked via test suites to this project
                $q->whereExists(function ($subq) use ($projectId) {
                    $subq->select(DB::raw(1))
                        ->from('test_suites')
                        ->whereRaw('test_cases.suite_id = test_suites.id')
                        ->where('test_suites.project_id', $projectId);
                });

                // OR test cases linked via stories to this project
                $q->orWhereExists(function ($subq) use ($projectId) {
                    $subq->select(DB::raw(1))
                        ->from('stories')
                        ->whereRaw('test_cases.story_id = stories.id')
                        ->where('stories.project_id', $projectId);
                });
            });
        }

        if (!empty($filters['story_id'])) {
            $query->where('test_cases.story_id', $filters['story_id']);
        }

        if (!empty($filters['suite_id'])) {
            $query->where('test_cases.suite_id', $filters['suite_id']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('test_cases.title', 'like', $searchTerm)
                    ->orWhere('test_cases.description', 'like', $searchTerm)
                    ->orWhere('test_cases.expected_results', 'like', $searchTerm);
            });
        }

        // 6. Apply sorting
        $sortField = $filters['sort'] ?? 'updated_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        $allowedSortFields = [
            'title' => 'test_cases.title',
            'updated_at' => 'test_cases.updated_at',
            'created_at' => 'test_cases.created_at',
            'priority' => 'test_cases.priority',
            'status' => 'test_cases.status'
        ];

        if (array_key_exists($sortField, $allowedSortFields)) {
            $query->orderBy($allowedSortFields[$sortField], $sortDirection);
        } else {
            $query->orderBy('test_cases.updated_at', 'desc');
        }

        // 7. Paginate and load relationships for display
        $testCases = $query->with([
            'testSuite:id,name,project_id',
            'testSuite.project:id,name',
            'story:id,title,project_id',
            'story.project:id,name'
        ])->paginate(15)->withQueryString();

        // Logging for debugging - check the SQL query
        Log::debug('TestCase query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => $testCases->total()
        ]);

        // After pagination but before returning:
        $testCases->getCollection()->transform(function ($testCase) {
            // Determine project_id from either test suite or story
            if ($testCase->testSuite && $testCase->testSuite->project) {
                $testCase->project_id = $testCase->testSuite->project->id;
                $testCase->project_name = $testCase->testSuite->project->name;
            } elseif ($testCase->story && $testCase->story->project) {
                $testCase->project_id = $testCase->story->project->id;
                $testCase->project_name = $testCase->story->project->name;
            }

            // Also add suite_name for consistency
            $testCase->suite_name = $testCase->testSuite->name ?? null;

            return $testCase;
        });

        // 8. Return data for the view
        return [
            'testCases' => $testCases,
            'projectsForFilter' => $projectsForFilter,
            'storiesForFilter' => $storiesForFilter,
            'suitesForFilter' => $suitesForFilter,
            'team' => $team,
            'filters' => $filters
        ];
    }


    /**
     * Get test cases for a specific project with optional filtering
     */
    public function getTestCasesForProject(Project $project, $filters = [])
    {
        // Extract filters
        $selectedSuiteId = $filters['suite_id'] ?? null;
        $selectedStoryId = $filters['story_id'] ?? null;
        $search = $filters['search'] ?? null;
        $sortField = $filters['sort'] ?? 'updated_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        $query = TestCase::query()
            ->with(['story', 'testSuite']);

        // Apply suite filter if present
        if (!empty($selectedSuiteId)) {
            $query->where('suite_id', $selectedSuiteId);
        }

        // Apply story filter if present
        if (!empty($selectedStoryId)) {
            $query->where('story_id', $selectedStoryId);
        }

        // Include both suite-linked & suite-less cases via story
        $query->where(function ($q) use ($project) {
            $q->whereHas(
                'testSuite',
                fn($q2) => $q2->where('project_id', $project->id)
            )
                ->orWhereDoesntHave('testSuite')
                ->whereHas(
                    'story',
                    fn($q2) => $q2->where('project_id', $project->id)
                );
        });

        // Add search functionality - more comprehensive search
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('expected_results', 'like', $searchTerm)
                    ->orWhereHas('story', function ($sq) use ($searchTerm) {
                        $sq->where('title', 'like', $searchTerm);
                    });
            });
        }

        // Sorting
        $allowedSortFields = ['title', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        // Make sure to paginate with withQueryString to preserve URL parameters
        $testCases = $query->paginate(10)->withQueryString();

        return [
            'testCases'  => $testCases,
            'testSuites' => $project->testSuites()->orderBy('name')->get(),
        ];
    }

    /**
     * Search for test cases that can be added to a test suite.
     * Returns test cases in the same project that aren't already in the test suite.
     * Modified to return all available cases when no search term is provided.
     */
    public function searchAvailableTestCases(Project $project, TestSuite $testSuite, array $filters = []): array
    {
        $search = $filters['search'] ?? null;
        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        // Base query for test cases in this project that aren't in this suite
        $query = TestCase::query()
            // Include all test cases that belong to the project via story
            ->whereHas('story', function ($q) use ($project) {
                $q->where('project_id', $project->id);
            })
            // And don't already belong to this suite
            ->where(function ($q) use ($testSuite) {
                $q->whereNull('suite_id')
                    ->orWhere('suite_id', '!=', $testSuite->id);
            });

        // Apply search if provided
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Order by title
        $query->orderBy('title');

        // For debugging when issues occur
        Log::debug('Test case search query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'project_id' => $project->id,
            'test_suite_id' => $testSuite->id
        ]);

        // Get paginated results with essential fields only
        $testCases = $query->select(['id', 'title', 'story_id', 'priority', 'status'])
            ->with(['story:id,title'])
            ->paginate($perPage, ['*'], 'page', $page);

        // Log the results for debugging
        Log::debug('Available test cases result', [
            'total' => $testCases->total(),
            'count' => count($testCases->items())
        ]);

        return [
            'test_cases' => $testCases->items(),
            'pagination' => [
                'total' => $testCases->total(),
                'per_page' => $testCases->perPage(),
                'current_page' => $testCases->currentPage(),
                'last_page' => $testCases->lastPage(),
            ],
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
    /**
     * Get a test case, ensuring it belongs to the project and optionally to a specific suite.
     */
    public function getTestCase(Project $project, ?TestSuite $testSuite, TestCase $testCase): TestCase
    {
        // Only validate the suite-test case relationship if a suite is specified
        if ($testSuite) {
            // Ensure test case belongs to the specified suite
            if ($testCase->suite_id !== $testSuite->id) {
                throw new \Exception('Test case not found in this test suite.');
            }

            // No need to check suite-project relationship as Laravel route model binding
            // already ensures testSuite belongs to project
        } else {
            // Check if test case belongs to this project through either its suite or story
            $belongsToProject = false;

            // Check suite path
            if ($testCase->suite_id) {
                $suite = $testCase->testSuite;
                if ($suite && $suite->project_id === $project->id) {
                    $belongsToProject = true;
                }
            }

            // Check story path
            if (!$belongsToProject && $testCase->story_id) {
                $story = $testCase->story;
                if ($story && $story->project_id === $project->id) {
                    $belongsToProject = true;
                }
            }

            if (!$belongsToProject) {
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
     * Update a test case, allowing reassignment to different suites and stories.
     */
    public function update(TestCase $testCase, array $data, Project $project): TestCase
    {
        // Save the current state before validating to allow suite/story changes
        $originalTestCase = clone $testCase;

        // Validate that the story exists and belongs to the project
        if (isset($data['story_id'])) {
            $story = Story::find($data['story_id']);
            if (!$story || $story->project_id !== $project->id) {
                throw new \Exception('The selected story does not belong to this project.');
            }
        }

        // Validate the suite if provided (null/empty is allowed)
        if (!empty($data['suite_id'])) {
            $suite = TestSuite::find($data['suite_id']);
            if (!$suite || $suite->project_id !== $project->id) {
                throw new \Exception('The selected test suite does not belong to this project.');
            }
        } else {
            // If empty string is provided, set to null for DB consistency
            $data['suite_id'] = null;
        }

        // Update the test case with new data
        $testCase->fill($data);

        // Log the changes for debugging
        Log::debug('Updating test case', [
            'test_case_id' => $testCase->id,
            'project_id' => $project->id,
            'old_suite_id' => $originalTestCase->suite_id,
            'new_suite_id' => $testCase->suite_id,
            'old_story_id' => $originalTestCase->story_id,
            'new_story_id' => $testCase->story_id
        ]);

        $testCase->save();

        Log::debug('Test Case Saved!');

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
