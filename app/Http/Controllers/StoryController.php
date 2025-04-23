<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Story;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
    /**
     * Authorization check (temporarily disabled like other controllers)
     */
    private function authorizeAccess($project): void
    {
        Log::warning('AUTHORIZATION CHECK IS TEMPORARILY DISABLED in StoryController@authorizeAccess');
    }

    /**
     * Display a listing of all stories across all projects (with filtering).
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
                'Story indexAll access failed: Session team invalid.',
                ['user_id' => Auth::id(), 'session_current_team' => $currentTeamId]
            );
            session()->forget('current_team');
            return redirect()->route('dashboard.select-team')->with('error', 'Invalid team selection. Please re-select.');
        }

        // Get project IDs the current user belongs to within this team
        $userProjectIds = Auth::user()->teams()
            ->where('teams.id', $currentTeamId)
            ->first()?->projects()->pluck('id');

        if (is_null($userProjectIds) || $userProjectIds->isEmpty()) {
            return view('dashboard.stories.index', [
                'stories' => collect(),
                'projects' => collect(),
                'team' => $team,
                'selectedProjectId' => null,
            ]);
        }

        // Get projects for filter dropdown
        $projects = Project::whereIn('id', $userProjectIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Handle project filter
        $filterProjectId = $request->input('project_id');

        // Base query for stories
        $query = Story::query()
            ->when($filterProjectId && $projects->contains('id', $filterProjectId), function ($query) use ($filterProjectId) {
                $query->whereHas('testCases', function ($q) use ($filterProjectId) {
                    $q->whereHas('testSuite', function ($q) use ($filterProjectId) {
                        $q->where('project_id', $filterProjectId);
                    });
                });
            })
            ->select('stories.*');

        // Add search query if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('external_id', 'like', $searchTerm);
            });
        }

        // Sort options
        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSortFields = ['title', 'created_at', 'updated_at', 'source', 'external_id'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        // Get paginated results
        $stories = $query->paginate(10)->withQueryString();

        return view('dashboard.stories.index', [
            'stories' => $stories,
            'projects' => $projects,
            'team' => $team,
            'selectedProjectId' => $filterProjectId,
            'searchTerm' => $request->search ?? '',
            'sortField' => $sortField,
            'sortDirection' => $sortDirection
        ]);
    }

    /**
     * Display a listing of stories for a specific project.
     */
    public function index(Project $project, Request $request)
    {
        $this->authorizeAccess($project);

        $query = Story::query()
            ->whereHas('testCases', function ($q) use ($project) {
                $q->whereHas('testSuite', function ($q) use ($project) {
                    $q->where('project_id', $project->id);
                });
            });

        // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('external_id', 'like', $searchTerm);
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSortFields = ['title', 'created_at', 'updated_at', 'source', 'external_id'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $stories = $query->paginate(10)->withQueryString();

        return view('dashboard.stories.index', [
            'stories' => $stories,
            'project' => $project,
            'searchTerm' => $request->search ?? '',
            'sortField' => $sortField,
            'sortDirection' => $sortDirection
        ]);
    }

    /**
     * Show the form for creating a new story.
     */
    public function create(Request $request)
    {
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Please select a team first.');
        }

        // Get all projects for the team
        $projects = Project::where('team_id', $currentTeamId)->get(['id', 'name']);

        // If a project_id is provided in the request, validate it belongs to the team
        $selectedProjectId = $request->input('project_id');
        $selectedProject = null;

        if ($selectedProjectId) {
            $selectedProject = $projects->firstWhere('id', $selectedProjectId);
            if (!$selectedProject) {
                return redirect()->route('dashboard.stories.create')
                    ->with('error', 'Selected project is not valid for your team.');
            }
        }

        return view('dashboard.stories.create', [
            'projects' => $projects,
            'selectedProject' => $selectedProject,
        ]);
    }

    /**
     * Store a newly created story.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'source' => 'required|string|in:manual,jira,github,azure',
            'external_id' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $story = new Story();
        $story->title = $request->input('title');
        $story->description = $request->input('description');
        $story->source = $request->input('source');
        $story->external_id = $request->input('external_id');
        $story->metadata = $request->input('metadata', []);
        $story->save();

        // Redirect to stories index
        return redirect()->route('dashboard.stories.indexAll')
            ->with('success', 'Story created successfully.');
    }

    /**
     * Display the specified story.
     */
    public function show(Story $story)
    {
        // Load related test cases
        $story->load('testCases.testSuite.project');

        return view('dashboard.stories.show', [
            'story' => $story,
        ]);
    }

    /**
     * Show the form for editing the specified story.
     */
    public function edit(Story $story)
    {
        return view('dashboard.stories.edit', [
            'story' => $story,
        ]);
    }

    /**
     * Update the specified story.
     */
    public function update(Request $request, Story $story)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'source' => 'required|string|in:manual,jira,github,azure',
            'external_id' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $story->title = $request->input('title');
        $story->description = $request->input('description');
        $story->source = $request->input('source');
        $story->external_id = $request->input('external_id');
        $story->metadata = $request->input('metadata', $story->metadata);
        $story->save();

        return redirect()->route('dashboard.stories.show', $story->id)
            ->with('success', 'Story updated successfully.');
    }

    /**
     * Remove the specified story.
     */
    public function destroy(Story $story)
    {
        $storyTitle = $story->title;

        // Check if there are test cases linked to this story
        $hasTestCases = $story->testCases()->exists();

        if ($hasTestCases) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete story - it has associated test cases."
                ], 422);
            }

            return redirect()->back()->with('error', 'Cannot delete story - it has associated test cases.');
        }

        $story->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Story \"$storyTitle\" deleted successfully."
            ]);
        }

        return redirect()->route('dashboard.stories.indexAll')
            ->with('success', "Story \"$storyTitle\" deleted successfully.");
    }
}
