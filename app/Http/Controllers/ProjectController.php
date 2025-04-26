<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use App\Models\TestExecution;
use App\Models\TestSuite;
use Illuminate\Http\Request;
use Illuminate\Support\Collection; // Import Collection
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass; // Or use array for $stats

class ProjectController extends Controller
{
    /**
     * TEMPORARILY DISABLED - Authorization check.
     */
    private function authorizeProjectTeamMembership(Project $project): void
    {
        // <<< AUTHORIZATION TEMPORARILY COMMENTED OUT FOR DEBUGGING >>>
        /*
        $user = Auth::user();
        if (!$user) {
            Log::warning('Authorization check failed: User not authenticated.', ['project_id' => $project->id]);
            abort(401, 'Unauthenticated.');
        }
        $isMember = $user->teams()->where('teams.id', $project->team_id)->exists();
        if (!$isMember) {
            Log::warning('Authorization failed: User not a member of the project team.', [
                'user_id' => $user->id, 'project_id' => $project->id,
                'required_team_id' => $project->team_id, 'user_teams' => $user->teams()->pluck('teams.id')->toArray()
            ]);
            abort(403, 'You are not a member of the team that owns this project.');
        }
        Log::debug('Authorization successful: User is a member of the project team.', [
            'user_id' => $user->id, 'project_id' => $project->id, 'team_id' => $project->team_id,
        ]);
        */
        Log::warning('AUTHORIZATION CHECK IS TEMPORARILY DISABLED in ProjectController@authorizeProjectTeamMembership');
    }

    /**
     * Display a listing of the projects for the current team.
     */
    public function index(Request $request)
    {
        $team = $this->getCurrentTeam($request);

        $projects = $team->projects()->withCount('testSuites')->orderBy('updated_at', 'desc')->get();
        $projects->each(function ($project) {
            // Load test suites with test case counts - more efficient way
            $project->test_cases_count = $project->testSuites()->withCount('testCases')->get(['id', 'project_id'])->sum('test_cases_count');
            // If you have a direct relationship defined (e.g., hasManyThrough testCases):
            // $proje ct->loadCount('testCases'); // Simpler if relationship exists
        });
        return view('dashboard.projects.index', compact('projects', 'team'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(Request $request)
    {
        $team = $this->getCurrentTeam($request);

        return view('dashboard.projects.create', compact('team'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $userId = Auth::id();
        $currentTeamId = $team->id;

        Log::debug('Attempting to store Project.', ['user_id' => $userId, 'team_id' => $currentTeamId]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'default_framework' => 'nullable|string|max:50',
            'auto_generate_tests' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            Log::warning('Project store validation failed.', ['user_id' => $userId, 'errors' => $validator->errors()->toArray()]);
            return redirect()->route('dashboard.projects.create')->withErrors($validator)->withInput();
        }
        try {
            $project = new Project();
            $project->name = $request->name;
            $project->description = $request->description;
            $project->team_id = $currentTeamId;
            $project->settings = [
                'default_framework' => $request->input('default_framework', 'selenium-python'),
                'auto_generate_tests' => $request->boolean('auto_generate_tests'),
                'container_timeout' => 600,
                'default_environment' => 'development',
            ];
            $project->save();
            Log::info('Project created successfully in DB.', ['user_id' => $userId, 'project_id' => $project->id, 'project_team_id_saved' => $project->team_id, 'session_current_team_before_save' => session('current_team') ?? 'NOT SET']);
            $request->session()->save();
            Log::debug('Session explicitly saved after project save, before redirect.', ['user_id' => $userId, 'project_id' => $project->id]);
            return redirect()->route('dashboard.projects.show', $project->id)->with('success', 'Project "' . $project->name . '" created successfully!');
        } catch (\Exception $e) {
            Log::error('Exception during project save.', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return redirect()->route('dashboard.projects.create')->with('error', 'Failed to create project. Please try again.')->withInput();
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $this->authorizeProjectTeamMembership($project); // Auth check (currently disabled)

        // Eager load relations efficiently
        // Use withCount to get the count without loading the full collection initially
        $project->loadCount('testSuites');
        $project->loadMissing([
            'team',
            // Load suites with test cases count needed for the tab and stats calculation
            'testSuites' => function ($query) {
                $query->withCount('testCases')->orderBy('updated_at', 'desc');
            },
            'projectIntegrations.integration',
            // testExecutions are loaded below
        ]);

        // --- Fetch Executions Separately ---
        $scriptIds = DB::table('test_scripts as ts')
            ->join('test_cases as tc', 'ts.test_case_id', '=', 'tc.id')
            ->join('test_suites as tsuite', 'tc.suite_id', '=', 'tsuite.id')
            ->where('tsuite.project_id', $project->id)
            ->pluck('ts.id');

        $executions = collect();
        if ($scriptIds->isNotEmpty()) {
            $executions = TestExecution::query()
                ->whereIn('script_id', $scriptIds)
                ->with(['testScript:id,name', 'initiator:id,name', 'status:id,name', 'environment:id,name'])
                ->select('id', 'script_id', 'initiator_id', 'environment_id', 'status_id', 'start_time', 'end_time', 'created_at')
                ->orderBy('start_time', 'desc')
                ->limit(50)
                ->get();
        }

        if (!$project) {
            abort(404, 'Project not found.');
        }
        Log::debug("Project data loaded in show", ['project_id' => $project->id, 'team_id' => $project->team_id, 'suite_count' => $project->test_suites_count]);

        // Pass the already loaded executions to stats and activities helpers
        $stats = $this->calculateProjectStats($project, $scriptIds, $executions);
        $recentActivities = $this->getRecentActivities($project, $executions);

        return view('dashboard.projects.show', compact(
            'project', // Contains team, testSuites (with test_cases_count), projectIntegrations
            'stats',
            'recentActivities',
            'executions' // Separate executions collection
        ));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $this->authorizeProjectTeamMembership($project); // Auth check (currently disabled)
        // Make sure the edit view exists and is correct
        return view('dashboard.projects.edit', compact('project'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorizeProjectTeamMembership($project); // Auth check (currently disabled)

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'settings.default_framework' => 'required|string|max:50',
            'settings.auto_generate_tests' => 'required|boolean',
            // Add other settings validation
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
            }
            // Redirect back to edit page on validation failure
            return redirect()->route('dashboard.projects.edit', $project->id)->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $project->name = $request->input('name');
            $project->description = $request->input('description');
            $settings = $project->settings ?? [];
            $settings['default_framework'] = $request->input('settings.default_framework');
            // Ensure boolean value is correctly processed
            $settings['auto_generate_tests'] = filter_var($request->input('settings.auto_generate_tests'), FILTER_VALIDATE_BOOLEAN);
            // Merge other settings...
            $project->settings = $settings;
            $project->save();
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Project settings updated.']);
            }
            // Redirect to show page on success
            return redirect()->route('dashboard.projects.show', $project->id)->with('success', 'Project updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating project ID {$project->id}: " . $e->getMessage());
            $errorMessage = 'Failed to update project settings.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            // Redirect back to edit page on error
            return redirect()->route('dashboard.projects.edit', $project->id)->with('error', $errorMessage)->withInput();
        }
    }


    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorizeProjectTeamMembership($project); // Auth check (currently disabled)
        $projectName = $project->name;

        DB::beginTransaction();
        try {
            $project->delete();
            DB::commit();

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => "Project \"$projectName\" deleted.", 'redirect' => route('dashboard.projects')]);
            }
            return redirect()->route('dashboard.projects')->with('success', "Project \"$projectName\" deleted.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting project ID {$project->id}: " . $e->getMessage());
            $errorMessage = "Failed to delete project \"$projectName\".";
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            return redirect()->route('dashboard.projects')->with('error', $errorMessage);
        }
    }

    // --- Helper Methods ---

    /**
     * Calculate statistics for the project overview.
     */
    private function calculateProjectStats(Project $project, Collection $scriptIds, Collection $executions): stdClass
    {
        $stats = new stdClass();
        // Use the count loaded in show() if available, otherwise count loaded relation
        $stats->totalTestSuites = $project->test_suites_count ?? $project->testSuites()->count();
        // Use the loaded testSuites relation which has test_cases_count eager loaded
        $stats->totalTestCases = $project->testSuites?->sum('test_cases_count') ?? 0;

        // Rest of the stats calculation using the passed $scriptIds and $executions
        if ($scriptIds->isEmpty() || $executions->isEmpty()) {
            $stats->passRate = 0;
            $stats->lastExecutionTime = 'Never';
            $stats->lastExecutionStatus = 'N/A';
            $stats->avgExecutionTimeSeconds = 0;
            $stats->avgExecutionTime = '-';
            $stats->totalExecutions = 0;
            $stats->passCount = 0;
            $stats->failCount = 0;
            $stats->executionHistory = [];
            return $stats;
        }

        $totalExecutions = $executions->count();
        $lastExecution = $executions->first(); // Assumes already sorted desc

        $passedStatuses = ['completed', 'passed'];
        $failedStatuses = ['failed', 'aborted', 'timeout', 'error'];
        $passCount = $executions->filter(fn($exec) => in_array(strtolower($exec->status?->name ?? ''), $passedStatuses))->count();
        $failCount = $executions->filter(fn($exec) => in_array(strtolower($exec->status?->name ?? ''), $failedStatuses))->count();
        $relevantExecutionsCount = $passCount + $failCount;

        $stats->passRate = $relevantExecutionsCount > 0 ? round(($passCount / $relevantExecutionsCount) * 100) : 0;
        $stats->lastExecutionTime = $lastExecution?->start_time?->diffForHumans() ?? 'Never';
        $stats->lastExecutionStatus = $lastExecution?->status?->name ? ucfirst($lastExecution->status->name) : 'N/A';
        $stats->totalExecutions = $totalExecutions;
        $stats->passCount = $passCount;
        $stats->failCount = $failCount;

        $avgDurationSeconds = $executions
            ->map(fn($exec) => $exec->start_time && $exec->end_time ? optional($exec->start_time)->diffInSeconds(optional($exec->end_time)) : null)
            ->filter()->avg();

        $stats->avgExecutionTimeSeconds = round($avgDurationSeconds ?? 0);
        $stats->avgExecutionTime = $this->formatDuration($stats->avgExecutionTimeSeconds);

        $historyLimit = now()->subDays(7);
        $dailyHistory = $executions
            ->whereNotNull('start_time')->where('start_time', '>=', $historyLimit)
            ->groupBy(fn($exec) => $exec->start_time->format('Y-m-d'))
            ->map(function ($dayExecutions) use ($passedStatuses, $failedStatuses) {
                return [
                    'passed' => $dayExecutions->filter(fn($exec) => in_array(strtolower($exec->status?->name ?? ''), $passedStatuses))->count(),
                    'failed' => $dayExecutions->filter(fn($exec) => in_array(strtolower($exec->status?->name ?? ''), $failedStatuses))->count(),
                ];
            })->sortKeys();
        $chartHistory = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartHistory[$date] = $dailyHistory->get($date, ['passed' => 0, 'failed' => 0]);
        }
        $stats->executionHistory = $chartHistory;

        return $stats;
    }

    /**
     * Get recent activity feed items for the project.
     * Accepts the already fetched executions collection.
     */
    private function getRecentActivities(Project $project, Collection $executions): Collection
    {
        $recentActivities = collect();
        $limit = 5;

        // Use the passed executions collection
        $executionActivities = $executions->take($limit)->map(function ($exec) { // Limit the pre-fetched collection
            if (!$exec) return null;
            $statusName = $exec->status?->name ?? 'Unknown';
            $statusClass = $this->getStatusColorClass($statusName);
            $scriptName = $exec->testScript?->name ?? 'Unknown Script';
            $initiatorName = $exec->initiator?->name ?? 'System';

            return (object)[
                'id' => 'exec-' . $exec->id,
                'type' => 'execution',
                'user' => $exec->initiator,
                'initiator_name' => $initiatorName,
                'description' => "Execution for <span class='font-medium'>{$scriptName}</span> finished with status <span class='font-medium {$statusClass}'>" . ucfirst($statusName) . "</span>",
                'timestamp' => $exec->start_time ?? $exec->created_at,
                'url' => '#'
            ];
        })->filter();
        if ($executionActivities) $recentActivities = $recentActivities->merge($executionActivities);

        // Use the already loaded suites from the 'show' method
        $suiteActivities = $project->testSuites?->sortByDesc('updated_at')->take($limit)->map(function ($suite) {
            if (!$suite) return null;
            // --- PROBLEM LINE ---
            $action = $suite->created_at?->eq($suite->updated_at) ? 'Created' : 'Updated';
            // --- END PROBLEM LINE ---
            $suiteShowUrl = route('dashboard.projects.test-suites.show', [$suite->project_id, $suite->id]);
            return (object)[
                'id' => 'suite-' . $suite->id,
                'type' => 'suite',
                'user' => null,
                'initiator_name' => 'User/System',
                'description' => "{$action} test suite <a href='{$suiteShowUrl}' class='font-medium text-indigo-600 dark:text-indigo-400 hover:underline'>{$suite->name}</a>",
                'timestamp' => $suite->updated_at,
                'url' => $suiteShowUrl
            ];
        })->filter();
        if ($suiteActivities) $recentActivities = $recentActivities->merge($suiteActivities);

        return $recentActivities->sortByDesc(function ($item) {
            return optional($item->timestamp)->timestamp ?? 0;
        })->take($limit)->values();
    }

    /**
     * Helper to format duration in seconds to human-readable string.
     */
    private function formatDuration(?int $seconds): string
    {
        if ($seconds === null || $seconds <= 0) return '-';
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return sprintf('%dh %dm %ds', $hours, $remainingMinutes, $remainingSeconds);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $remainingSeconds);
        } else {
            return sprintf('%ds', $remainingSeconds);
        }
    }

    /**
     * Helper to get Tailwind color class based on execution status name.
     */
    private function getStatusColorClass(?string $status): string
    {
        $statusLower = strtolower($status ?? '');
        return match ($statusLower) {
            'completed', 'passed' => 'text-green-600 dark:text-green-400',
            'failed', 'aborted', 'timeout', 'error' => 'text-red-600 dark:text-red-400',
            'running' => 'text-blue-600 dark:text-blue-400',
            'pending', 'queued' => 'text-yellow-600 dark:text-yellow-400',
            default => 'text-zinc-500 dark:text-zinc-400',
        };
    }
}
