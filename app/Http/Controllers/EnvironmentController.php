<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EnvironmentController extends Controller
{
    /**
     * Display a listing of environments.
     */
    public function index()
    {
        $currentTeamId = session('current_team');

        // Get global environments
        $globalEnvironments = Environment::where('is_global', true)
            ->orderBy('name')
            ->get();

        // Get team-specific environments via projects
        $teamProjects = Project::where('team_id', $currentTeamId)->pluck('id');
        $teamEnvironments = Environment::where('is_global', false)
            ->whereHas('projects', function($query) use ($teamProjects) {
                $query->whereIn('projects.id', $teamProjects);
            })
            ->orderBy('name')
            ->get();

        return view('dashboard.environments.index', [
            'globalEnvironments' => $globalEnvironments,
            'teamEnvironments' => $teamEnvironments
        ]);
    }

    /**
     * Show the form for creating a new environment.
     */
    public function create()
    {
        $currentTeamId = session('current_team');
        $projects = Project::where('team_id', $currentTeamId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('dashboard.environments.create', [
            'projects' => $projects
        ]);
    }

    /**
     * Store a newly created environment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'is_global' => 'boolean',
            'is_active' => 'boolean',
            'projects' => 'required_unless:is_global,1|array',
            'configuration' => 'nullable|array',
            'configuration.*.key' => 'required|string',
            'configuration.*.value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Convert configuration from key-value format to associative array
        $configArray = [];
        if ($request->has('configuration')) {
            foreach ($request->configuration as $item) {
                if (isset($item['key']) && isset($item['value'])) {
                    $configArray[$item['key']] = $item['value'];
                }
            }
        }

        // Create the environment
        $environment = Environment::create([
            'name' => $request->name,
            'is_global' => $request->boolean('is_global', false),
            'is_active' => $request->boolean('is_active', true),
            'configuration' => $configArray,
        ]);

        // Attach projects if not global
        if (!$request->boolean('is_global') && $request->has('projects')) {
            $environment->projects()->attach($request->projects);
        }

        return redirect()->route('dashboard.environments.index')
            ->with('success', 'Environment "' . $environment->name . '" created successfully.');
    }

    /**
     * Display the specified environment.
     */
    public function show(Environment $environment)
    {
        $environment->load('projects');

        return view('dashboard.environments.show', [
            'environment' => $environment
        ]);
    }

    /**
     * Show the form for editing the environment.
     */
    public function edit(Environment $environment)
    {
        $currentTeamId = session('current_team');
        $projects = Project::where('team_id', $currentTeamId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedProjects = $environment->projects->pluck('id')->toArray();

        return view('dashboard.environments.edit', [
            'environment' => $environment,
            'projects' => $projects,
            'selectedProjects' => $selectedProjects
        ]);
    }

    /**
     * Update the specified environment.
     */
    public function update(Request $request, Environment $environment)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'is_global' => 'boolean',
            'is_active' => 'boolean',
            'projects' => 'required_unless:is_global,1|array',
            'configuration' => 'nullable|array',
            'configuration.*.key' => 'required|string',
            'configuration.*.value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Convert configuration from key-value format to associative array
        $configArray = [];
        if ($request->has('configuration')) {
            foreach ($request->configuration as $item) {
                if (isset($item['key']) && isset($item['value'])) {
                    $configArray[$item['key']] = $item['value'];
                }
            }
        }

        // Update the environment
        $environment->update([
            'name' => $request->name,
            'is_global' => $request->boolean('is_global', false),
            'is_active' => $request->boolean('is_active', true),
            'configuration' => $configArray,
        ]);

        // Sync projects
        if (!$request->boolean('is_global')) {
            $environment->projects()->sync($request->projects);
        } else {
            $environment->projects()->detach();
        }

        return redirect()->route('dashboard.environments.show', $environment->id)
            ->with('success', 'Environment updated successfully.');
    }

    /**
     * Remove the specified environment.
     */
    public function destroy(Environment $environment)
    {
        $name = $environment->name;

        // Check if there are any executions using this environment
        $hasExecutions = \App\Models\TestExecution::where('environment_id', $environment->id)->exists();

        if ($hasExecutions) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete environment - it has associated test executions."
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Cannot delete environment - it has associated test executions.');
        }

        // Detach projects first
        $environment->projects()->detach();

        // Delete the environment
        $environment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Environment \"$name\" deleted successfully."
            ]);
        }

        return redirect()->route('dashboard.environments.index')
            ->with('success', "Environment \"$name\" deleted successfully.");
    }
}
