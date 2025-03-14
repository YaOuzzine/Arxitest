<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use App\Models\Environment;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $teamIds = $user->teams()->pluck('teams.id');

        $query = Project::whereIn('team_id', $teamIds)
            ->withCount(['testSuites']);

        // Filter by team if provided
        if ($request->has('team_id') && $request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Load the projects with eager loading
        $projects = $query->with(['team', 'environments', 'testSuites' => function($query) {
            $query->withCount('testScripts');
        }])
        ->orderBy('updated_at', 'desc')
        ->paginate(9);

        // Calculate test scripts count for each project
        $projects->each(function($project) {
            $project->test_scripts_count = $project->testSuites->sum('test_scripts_count');
        });

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        return view('projects.create', [
            'teams' => Auth::user()->teams,
            'environments' => Environment::where('is_active', true)->get(),
            'aiTemplates' => $this->getAITemplates(),
            'integrations' => Integration::where('is_active', true)->get(),
        ]);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_id' => [
                'required',
                Rule::exists('team_user', 'team_id')->where('user_id', Auth::id())
            ],
            'settings' => 'array',
            'settings.ai_enabled' => 'boolean',
            'settings.ai_provider' => 'required_if:settings.ai_enabled,true',
            'environments' => 'array',
            'environments.*' => 'exists:environments,id',
            'integrations' => 'array',
            'integrations.*' => 'exists:integrations,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $project = Project::create([
                'team_id' => $request->team_id,
                'name' => $request->name,
                'description' => $request->description,
                'settings' => $this->mergeSettings($request->settings ?? []),
            ]);

            // Attach environments and integrations
            if ($request->has('environments')) {
                $project->environments()->sync($request->environments);
            }

            if ($request->has('integrations')) {
                $project->integrations()->sync($request->integrations);
            }

            // AI Initialization
            if (isset($project->settings['ai_enabled']) && $project->settings['ai_enabled']) {
                $this->initializeAIComponents($project);
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Project creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        // Check if user has access to the project
        if (!$this->checkProjectAccess($project)) {
            abort(403, 'Unauthorized access to this project.');
        }

        $executionStats = $this->getExecutionStats($project);
        $aiRecommendations = $this->getAIRecommendations($project);

        return view('projects.show', compact('project', 'executionStats', 'aiRecommendations'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        // Check if user has access to the project
        if (!$this->checkProjectAccess($project)) {
            abort(403, 'Unauthorized access to this project.');
        }

        return view('projects.edit', [
            'project' => $project,
            'teams' => Auth::user()->teams,
            'environments' => Environment::all(),
            'integrations' => Integration::where('is_active', true)->get(),
            'aiTemplates' => $this->getAITemplates(),
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        // Check if user has access to the project
        if (!$this->checkProjectAccess($project)) {
            abort(403, 'Unauthorized access to this project.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_id' => [
                'required',
                Rule::exists('team_user', 'team_id')->where('user_id', Auth::id())
            ],
            'settings' => 'array',
            'settings.ai_enabled' => 'boolean',
            'settings.ai_provider' => 'required_if:settings.ai_enabled,true',
            'environments' => 'array',
            'environments.*' => 'exists:environments,id',
            'integrations' => 'array',
            'integrations.*' => 'exists:integrations,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $project->update([
                'team_id' => $request->team_id,
                'name' => $request->name,
                'description' => $request->description,
                'settings' => $this->mergeSettings($request->settings ?? [], $project->settings ?? []),
            ]);

            // Update relationships
            if ($request->has('environments')) {
                $project->environments()->sync($request->environments);
            }

            if ($request->has('integrations')) {
                $project->integrations()->sync($request->integrations);
            }

            // Handle AI changes
            if (isset($project->settings['ai_enabled']) && $project->settings['ai_enabled']) {
                $this->updateAIComponents($project);
            } else {
                $this->disableAIComponents($project);
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Project update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        // Check if user has access to the project
        if (!$this->checkProjectAccess($project)) {
            abort(403, 'Unauthorized access to this project.');
        }

        try {
            $this->cleanupAIResources($project);
            $project->delete();

            return redirect()->route('projects.index')
                ->with('success', 'Project deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Project deletion failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if the authenticated user has access to the project
     */
    private function checkProjectAccess(Project $project)
    {
        $user = Auth::user();
        $teamIds = $user->teams()->pluck('teams.id');

        return $teamIds->contains($project->team_id);
    }

    /**
     * Merge settings with defaults and existing values
     */
    private function mergeSettings($newSettings, $existingSettings = [])
    {
        $defaults = [
            'ai_enabled' => false,
            'ai_provider' => 'openai',
            'test_generation' => 'semi-automatic',
            'version_control' => true,
            'notification_settings' => [
                'on_failure' => true,
                'on_completion' => true,
            ]
        ];

        // Handle notification settings separately to avoid overwriting nested arrays
        $mergedSettings = array_merge($defaults, $existingSettings, $newSettings ?? []);

        // Ensure notification_settings is properly merged
        if (isset($newSettings['notification_settings']) && is_array($newSettings['notification_settings'])) {
            $mergedSettings['notification_settings'] = array_merge(
                $defaults['notification_settings'],
                isset($existingSettings['notification_settings']) ? $existingSettings['notification_settings'] : [],
                $newSettings['notification_settings']
            );
        }

        return $mergedSettings;
    }

    /**
     * Get available AI templates
     */
    private function getAITemplates()
    {
        return [
            'basic' => 'Basic Test Structure',
            'bdd' => 'Behavior-Driven Development',
            'e2e' => 'End-to-End Testing',
            'performance' => 'Performance Testing',
        ];
    }

    /**
     * Initialize AI components for a project
     */
    private function initializeAIComponents(Project $project)
    {
        // Implementation for AI initialization
        // This could connect to AI services, create templates, etc.
    }

    /**
     * Update AI components for a project
     */
    private function updateAIComponents(Project $project)
    {
        // Implementation for AI configuration updates
    }

    /**
     * Disable AI components for a project
     */
    private function disableAIComponents(Project $project)
    {
        // Implementation for disabling AI features
    }

    /**
     * Clean up AI resources for a project
     */
    private function cleanupAIResources(Project $project)
    {
        // Implementation for cleaning up AI-related resources
    }

    /**
     * Get execution statistics for a project
     */
    private function getExecutionStats(Project $project)
    {
        // You would implement real statistics gathering here
        return [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'running' => 0,
            'average_duration' => '0s',
        ];
    }

    /**
     * Get AI recommendations for a project
     */
    private function getAIRecommendations(Project $project)
    {
        // Implementation for generating AI recommendations
        return [];
    }
}
