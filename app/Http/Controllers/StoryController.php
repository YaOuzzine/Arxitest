<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoryRequest;
use App\Models\Project;
use App\Models\Story;
use App\Services\StoryService;
use Illuminate\Http\Request;
use App\Traits\JsonResponse;

class StoryController extends Controller
{
    use JsonResponse;

    protected $storyService;

    public function __construct(StoryService $storyService)
    {
        $this->storyService = $storyService;
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

    public function create(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $projects = $this->storyService->getProjectsForTeam($team);

        $selectedProjectId = $request->input('project_id');
        $selectedProject = null;

        if ($selectedProjectId) {
            $selectedProject = $projects->firstWhere('id', $selectedProjectId);
        }

        return view('dashboard.stories.create', [
            'projects' => $projects,
            'selectedProject' => $selectedProject,
        ]);
    }

    public function store(StoryRequest $request)
    {
        $story = $this->storyService->createStory($request->validated());

        return redirect()->route('dashboard.stories.indexAll')
            ->with('success', 'Story created successfully.');
    }

    public function show(Story $story)
    {
        $testCases = $this->storyService->getTestCasesForStory($story);

        return view('dashboard.stories.show', [
            'story' => $story,
            'testCases' => $testCases
        ]);
    }

    public function edit(Story $story)
    {
        return view('dashboard.stories.edit', [
            'story' => $story,
        ]);
    }

    public function update(StoryRequest $request, Story $story)
    {
        $this->storyService->updateStory($story, $request->validated());

        return redirect()->route('dashboard.stories.show', $story->id)
            ->with('success', 'Story updated successfully.');
    }

    public function destroy(Story $story)
    {
        try {
            $storyTitle = $story->title;
            $this->storyService->deleteStory($story);

            if (request()->expectsJson()) {
                return $this->successResponse([], "Story \"$storyTitle\" deleted successfully.");
            }

            return redirect()->route('dashboard.stories.indexAll')
                ->with('success', "Story \"$storyTitle\" deleted successfully.");
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return $this->errorResponse($e->getMessage(), 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
