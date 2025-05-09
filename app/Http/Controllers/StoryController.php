<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoryRequest;
use App\Http\Requests\UpdateStoryRequest;
use App\Models\Project;
use App\Models\Story;
use App\Services\StoryService;
use Illuminate\Http\Request;
use App\Traits\JsonResponse;
use App\Http\Requests\StoreStoryRequest;
use Illuminate\Support\Facades\Validator;
use App\Services\AI\AIGenerationService;
use Illuminate\Support\Facades\Log;

class StoryController extends Controller
{
    use JsonResponse;

    protected $storyService;

    public function __construct(StoryService $storyService)
    {
        $this->storyService = $storyService;
    }

    public function getJsonForProject(Project $project)
    {
        $stories = $project
            ->stories()
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json([
            'success'  => true,
            'stories'  => $stories,
        ]);
    }

    public function indexAll(Request $request)
    {
        $team = $this->getCurrentTeam($request);

        // Get projects for filter dropdown
        $projects = $this->storyService->getProjectsForTeam($team);

        // Get all available story sources
        $sources = ['manual', 'jira', 'github', 'azure'];

        // Parse selected sources from the request
        $selectedSources = $request->has('sources') ? $request->sources : [];
        if (!is_array($selectedSources)) {
            $selectedSources = [$selectedSources];
        }

        // Build filters array
        $filters = [
            'project_id' => $request->input('project_id'),
            'test_case_id' => $request->input('test_case_id'),
            'sources' => $selectedSources,
            'search' => $request->input('search'),
            'sort' => $request->input('sort', 'updated_at'),
            'direction' => $request->input('direction', 'desc')
        ];

        // Get filtered stories
        $stories = $this->storyService->getStoriesForTeam($team, $filters);

        return view('dashboard.stories.index', [
            'stories' => $stories,
            'projects' => $projects,
            'team' => $team,
            'selectedProjectId' => $request->input('project_id'),
            'sources' => $sources,
            'selectedSources' => $selectedSources,
            'searchTerm' => $request->input('search', ''),
            'sortField' => $filters['sort'],
            'sortDirection' => $filters['direction']
        ]);
    }

    public function index(Project $project, Request $request)
    {
        $team = $this->getCurrentTeam($request);

        // Ensure project belongs to team
        if ($project->team_id !== $team->id) {
            return redirect()->route('dashboard.stories.indexAll')
                ->with('error', 'Invalid project selected.');
        }

        // Get all available story sources
        $sources = ['manual', 'jira', 'github', 'azure'];

        // Parse selected sources from the request
        $selectedSources = $request->has('sources') ? $request->sources : [];
        if (!is_array($selectedSources)) {
            $selectedSources = [$selectedSources];
        }

        // Build filters array
        $filters = [
            'project_id' => $project->id,
            'test_case_id' => $request->input('test_case_id'),
            'sources' => $selectedSources,
            'search' => $request->input('search'),
            'sort' => $request->input('sort', 'updated_at'),
            'direction' => $request->input('direction', 'desc')
        ];

        // Get filtered stories
        $stories = $this->storyService->getStoriesForTeam($team, $filters);

        return view('dashboard.stories.index', [
            'stories' => $stories,
            'project' => $project,
            'sources' => $sources,
            'selectedSources' => $selectedSources,
            'searchTerm' => $request->input('search', ''),
            'sortField' => $filters['sort'],
            'sortDirection' => $filters['direction']
        ]);
    }

    /**
     * Show the form for creating a new story.
     */
    public function create(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $projects = $this->storyService->getProjectsForTeam($team);

        $selectedProjectId = $request->input('project_id');
        $selectedProject = null;
        $epics = collect();

        if ($selectedProjectId) {
            $selectedProject = $projects->firstWhere('id', $selectedProjectId);
            if ($selectedProject) {
                $epics = $this->storyService->getEpicsForProject($selectedProject);
            }
        }

        return view('dashboard.stories.create', [
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'epics' => $epics,
        ]);
    }

    /**
     * Store a newly created story in storage.
     */
    public function store(StoreStoryRequest $request)
    {
        try {
            $story = $this->storyService->createStory($request->validated());

            if ($request->expectsJson()) {
                return $this->successResponse(
                    ['story' => $story, 'redirect' => route('dashboard.stories.show', $story->id)],
                    'Story created successfully'
                );
            }

            return redirect()->route('dashboard.stories.show', $story->id)
                ->with('success', 'Story created successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return $this->errorResponse($e->getMessage(), 422);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function getEpics(Project $project)
    {
        try {
            // Comment out the authorization temporarily for debugging
            // $this->authorizeAccess($project);

            $epics = $this->storyService->getEpicsForProject($project);
            return $this->successResponse(['epics' => $epics]);
        } catch (\Exception $e) {

            // Log the error for debugging
            Log::error('Epic fetch error: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Generate a story using AI
     */
    public function generateWithAI(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|min:20|max:2000',
            'project_id' => 'required|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            // Set up context
            $context = [
                'project_id' => $request->input('project_id'),
                'epic_id' => $request->input('epic_id'),
            ];

            // Generate the story using the service
            $aiService = app(AIGenerationService::class);
            $story = $aiService->generateStory($request->input('prompt'), $context);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $story->id,
                    'title' => $story->title,
                    'description' => $story->description,
                    'acceptance_criteria' => $story->metadata['acceptance_criteria'] ?? [],
                    'priority' => $story->metadata['priority'] ?? 'medium',
                    'tags' => $story->metadata['tags'] ?? [],
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate story: ' . $e->getMessage(), 500);
        }
    }

    public function show(Story $story)
    {
        $testCases = $this->storyService->getTestCasesForStory($story);
        return view('dashboard.stories.show', [
            'story' => $story,
            'testCases' => $testCases
        ]);
    }

    /**
     * Show the form for editing the specified story.
     */
    public function edit(Story $story, Request $request)
    {
        $team = $this->getCurrentTeam($request);

        // Get current project
        $project = $story->project;

        // Make sure project belongs to current team
        if ($project->team_id !== $team->id) {
            return redirect()->route('dashboard.stories.indexAll')
                ->with('error', 'You do not have permission to edit this story.');
        }

        // Get available epics for the project
        $epics = $this->storyService->getEpicsForProject($project);

        return view('dashboard.stories.edit', [
            'story' => $story,
            'project' => $project,
            'epics' => $epics,
        ]);
    }

    /**
     * Update the specified story in storage.
     */
    public function update(UpdateStoryRequest $request, Story $story)
    {
        try {
            // Get only the fields we want to update
            $data = $request->validated();

            // Ensure project_id is set for validation in service
            $data['project_id'] = $story->project_id;

            // Update the story via service
            $story = $this->storyService->updateStory($story, $data);

            if ($request->expectsJson()) {
                return $this->successResponse(['story' => $story], 'Story updated successfully');
            }

            return redirect()->route('dashboard.stories.show', $story->id)
                ->with('success', 'Story updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return $this->errorResponse($e->getMessage(), 422);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Story $story, Request $request)
    {
        try {
            $force = $request->has('force') && $request->force === 'true';
            $result = $this->storyService->deleteStory($story, $force);

            if (!$result['success']) {
                // If not successful and there are test cases, return 409 Conflict with test cases
                return response()->json($result, 409);
            }

            if (request()->expectsJson()) {
                return response()->json($result);
            }

            return redirect()->route('dashboard.stories.indexAll')
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
