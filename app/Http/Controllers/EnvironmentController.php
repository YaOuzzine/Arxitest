<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\JsonResponse;

class EnvironmentController extends Controller
{
    use JsonResponse;
    /**
     * Display a listing of environments.
     */
    public function index(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        // Get global environments
        $globalEnvironments = Environment::where('is_global', true)
            ->orderBy('name')
            ->get();

        // Get team-specific environments via projects
        $teamProjects = Project::where('team_id', $currentTeamId)->pluck('id');
        $teamEnvironments = Environment::where('is_global', false)
            ->whereHas('projects', function ($query) use ($teamProjects) {
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
    public function create(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;
        $projects = Project::where('team_id', $currentTeamId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('dashboard.environments.create', [
            'projects' => $projects
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Explicitly normalize boolean values from checkboxes
            $normalizedData = $request->all();
            $normalizedData['is_global'] = $request->has('is_global');
            $normalizedData['is_active'] = $request->has('is_active');

            // Define validation rules
            $rules = [
                'name' => 'required|string|max:100',
                'is_global' => 'boolean',
                'is_active' => 'boolean',
                'configuration' => 'nullable|array',
                'configuration.*.key' => 'required|string',
                'configuration.*.value' => 'nullable|string',
            ];

            // Only require projects if the environment is not global
            if (!$normalizedData['is_global']) {
                $rules['projects'] = 'required|array|min:1';
            }

            $validator = Validator::make($normalizedData, $rules);

            if ($validator->fails()) {
                // Get specific error messages
                $errorMessages = $validator->errors()->all();
                $errorSummary = implode(' ', $errorMessages);

                Log::warning('Environment creation validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'input' => $request->except(['configuration']),
                    'is_global' => $normalizedData['is_global']
                ]);

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please fix the following errors: ' . $errorSummary);
            }

            // Convert configuration from key-value format to associative array
            $configArray = [];
            $hasBaseUrl = false;

            if ($request->has('configuration')) {
                foreach ($request->configuration as $item) {
                    if (isset($item['key']) && !empty(trim($item['key']))) {
                        $configArray[$item['key']] = $item['value'] ?? '';

                        // Check if BASE_URL is present
                        if (strtoupper(trim($item['key'])) === 'BASE_URL') {
                            $hasBaseUrl = true;
                        }
                    }
                }
            }

            // Add BASE_URL if not provided
            if (!$hasBaseUrl) {
                $configArray['BASE_URL'] = 'http://localhost:8000';
            }

            // Create the environment
            $environment = Environment::create([
                'name' => $request->input('name'),
                'is_global' => $normalizedData['is_global'],
                'is_active' => $normalizedData['is_active'],
                'configuration' => $configArray,
            ]);

            // Attach projects if not global
            if (!$normalizedData['is_global'] && $request->has('projects')) {
                $environment->projects()->attach($request->projects);
            }

            Log::info('Environment created successfully', [
                'environment_id' => $environment->id,
                'name' => $environment->name,
                'is_global' => $environment->is_global,
                'config_count' => count($configArray)
            ]);

            return redirect()->route('dashboard.environments.index')
                ->with('success', 'Environment "' . $environment->name . '" created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create environment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create environment: ' . $e->getMessage());
        }
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
    public function edit(Request $request, Environment $environment)
    {
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;
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
                return $this->errorResponse("Cannot delete environment - it has associated test executions.", 422);
            }

            return redirect()->back()
                ->with('error', 'Cannot delete environment - it has associated test executions.');
        }

        // Detach projects first
        $environment->projects()->detach();

        // Delete the environment
        $environment->delete();

        if (request()->expectsJson()) {
            if (request()->expectsJson()) {
                return $this->successResponse([], "Environment \"$name\" deleted successfully.");
            }
        }

        return redirect()->route('dashboard.environments.index')
            ->with('success', "Environment \"$name\" deleted successfully.");
    }
}
