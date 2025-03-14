<?php

namespace App\Http\Controllers;

use App\Models\TestExecution;
use App\Models\TestScript;
use App\Models\Environment;
use App\Models\ExecutionStatus;
use App\Models\Container;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TestExecutionController extends Controller
{
    /**
     * Display a listing of the test executions.
     */
    public function index(Request $request)
    {
        $query = TestExecution::with(['testScript.testSuite', 'environment', 'executionStatus', 'initiator']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status_id', $request->status);
        }

        // Filter by environment
        if ($request->has('environment') && $request->environment) {
            $query->where('environment_id', $request->environment);
        }

        // Filter by date range
        if ($request->has('date_range')) {
            if ($request->date_range === 'today') {
                $query->whereDate('start_time', Carbon::today());
            } elseif ($request->date_range === 'week') {
                $query->where('start_time', '>=', Carbon::now()->startOfWeek());
            } elseif ($request->date_range === 'month') {
                $query->where('start_time', '>=', Carbon::now()->startOfMonth());
            } elseif ($request->date_range === 'custom' && $request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('start_time', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->whereHas('testScript', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            })->orWhereHas('environment', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        // Only show executions initiated by the current user or their team
        $user = Auth::user();
        $teamIds = $user->teams()->pluck('teams.id');

        $query->where(function ($q) use ($user, $teamIds) {
            $q->where('initiator_id', $user->id)
              ->orWhereHas('testScript.testSuite.project', function ($q) use ($teamIds) {
                  $q->whereIn('team_id', $teamIds);
              });
        });

        // Order by most recent first
        $query->orderBy('start_time', 'desc');

        $testExecutions = $query->paginate(10);

        // Get statistics
        $stats = $this->getExecutionStats();

        return view('test-executions.index', compact('testExecutions', 'stats'));
    }

    /**
     * Show the form for creating a new test execution.
     */
    public function create()
    {
        return view('test-executions.create');
    }

    /**
     * Store a newly created test execution in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'script_id' => 'required|exists:test_scripts,id',
            'environment_id' => 'required|exists:environments,id',
            'run_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the running status
        $runningStatus = ExecutionStatus::where('name', 'Running')->firstOrFail();

        // Create the test execution
        $testExecution = TestExecution::create([
            'script_id' => $request->script_id,
            'initiator_id' => Auth::id(),
            'environment_id' => $request->environment_id,
            'status_id' => $runningStatus->id,
            'start_time' => now(),
        ]);

        // Create a container for this execution
        $container = Container::create([
            'execution_id' => $testExecution->id,
            'container_id' => 'container-' . Str::random(10), // This would be the actual container ID in a real system
            'status' => 'running',
            'configuration' => [
                'resources' => [
                    'cpu' => '1 Core',
                    'memory' => '2GB'
                ],
                'priority' => $request->has('priority'),
                'notes' => $request->run_notes,
            ],
            'start_time' => now(),
        ]);

        // In a real application, you would call your container orchestration service here
        // to actually start the container and run the test

        // For this example, we'll just display the execution details
        return redirect()->route('test-executions.show', $testExecution->id)
            ->with('success', 'Test execution started successfully!');
    }

    /**
     * Display the specified test execution.
     */
    public function show(TestExecution $testExecution)
    {
        $testExecution->load(['testScript.testSuite', 'environment', 'executionStatus', 'initiator', 'containers.resourceMetrics']);

        return view('test-executions.show', compact('testExecution'));
    }

    /**
     * Show the form for editing the specified test execution.
     *
     * Note: Executions typically can't be edited, only viewed or canceled.
     */
    public function edit(TestExecution $testExecution)
    {
        // Redirect to show view since executions can't be edited
        return redirect()->route('test-executions.show', $testExecution->id);
    }

    /**
     * Update the specified test execution in storage.
     *
     * Note: This would be used for administrative purposes only,
     * such as marking a stuck execution as complete.
     */
    public function update(Request $request, TestExecution $testExecution)
    {
        // Only allow admins to update executions manually
        if (!Auth::user()->role === 'admin') {
            return redirect()->route('test-executions.show', $testExecution->id)
                ->with('error', 'You do not have permission to update executions.');
        }

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:execution_statuses,id',
            'end_time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $testExecution->update([
            'status_id' => $request->status_id,
            'end_time' => $request->end_time ?? now(),
        ]);

        return redirect()->route('test-executions.show', $testExecution->id)
            ->with('success', 'Test execution updated successfully!');
    }

    /**
     * Remove the specified test execution from storage.
     *
     * Note: This is used to cancel a running execution.
     */
    public function destroy(TestExecution $testExecution)
    {
        // Only allow canceling if it's still running
        if ($testExecution->executionStatus->name !== 'Running') {
            return redirect()->route('test-executions.show', $testExecution->id)
                ->with('error', 'You can only cancel running executions.');
        }

        // Make sure the user is allowed to cancel this execution
        $user = Auth::user();
        if ($testExecution->initiator_id !== $user->id && $user->role !== 'admin') {
            return redirect()->route('test-executions.show', $testExecution->id)
                ->with('error', 'You do not have permission to cancel this execution.');
        }

        // Get the canceled status
        $canceledStatus = ExecutionStatus::where('name', 'Canceled')->first();
        if (!$canceledStatus) {
            // If no canceled status exists, use a failed status
            $canceledStatus = ExecutionStatus::where('name', 'Failed')->firstOrFail();
        }

        // Update execution status
        $testExecution->update([
            'status_id' => $canceledStatus->id,
            'end_time' => now(),
        ]);

        // Update container status
        foreach ($testExecution->containers as $container) {
            if ($container->status === 'running') {
                $container->update([
                    'status' => 'terminated',
                    'end_time' => now(),
                ]);
            }
        }

        // In a real application, you would call your container orchestration service here
        // to actually terminate the containers

        return redirect()->route('test-executions.index')
            ->with('success', 'Test execution canceled successfully!');
    }

    /**
     * Get execution statistics for the dashboard.
     */
    private function getExecutionStats()
    {
        $user = Auth::user();
        $teamIds = $user->teams()->pluck('teams.id');

        // Period for comparison (last 30 days)
        $currentPeriodStart = Carbon::now()->subDays(30);
        $previousPeriodStart = Carbon::now()->subDays(60);

        // Get executions for current period
        $currentExecutions = TestExecution::where(function ($q) use ($user, $teamIds) {
            $q->where('initiator_id', $user->id)
              ->orWhereHas('testScript.testSuite.project', function ($q) use ($teamIds) {
                  $q->whereIn('team_id', $teamIds);
              });
        })
        ->where('start_time', '>=', $currentPeriodStart)
        ->get();

        // Get executions for previous period
        $previousExecutions = TestExecution::where(function ($q) use ($user, $teamIds) {
            $q->where('initiator_id', $user->id)
              ->orWhereHas('testScript.testSuite.project', function ($q) use ($teamIds) {
                  $q->whereIn('team_id', $teamIds);
              });
        })
        ->whereBetween('start_time', [$previousPeriodStart, $currentPeriodStart])
        ->get();

        // Calculate stats
        $currentTotal = $currentExecutions->count();
        $previousTotal = $previousExecutions->count();

        $currentPassed = $currentExecutions->filter(function ($execution) {
            return $execution->executionStatus->name === 'Passed';
        })->count();

        $previousPassed = $previousExecutions->filter(function ($execution) {
            return $execution->executionStatus->name === 'Passed';
        })->count();

        // Calculate average duration
        $completedExecutions = $currentExecutions->filter(function ($execution) {
            return $execution->start_time && $execution->end_time;
        });

        $totalDuration = $completedExecutions->sum(function ($execution) {
            return $execution->start_time->diffInSeconds($execution->end_time);
        });

        $avgDuration = $completedExecutions->count() > 0
            ? round($totalDuration / $completedExecutions->count())
            : 0;

        // Format the duration nicely
        $formattedDuration = $this->formatDuration($avgDuration);

        // Calculate trends
        $trend = $previousTotal > 0
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100)
            : 0;

        $successRate = $currentTotal > 0
            ? round(($currentPassed / $currentTotal) * 100)
            : 0;

        $previousSuccessRate = $previousTotal > 0
            ? round(($previousPassed / $previousTotal) * 100)
            : 0;

        $successTrend = $previousSuccessRate > 0
            ? $successRate - $previousSuccessRate
            : 0;

        // Calculate resource usage
        $resourceUsage = $this->calculateResourceUsage($user);

        // Return the stats
        return [
            'total' => $currentTotal,
            'passed' => $currentPassed,
            'trend' => $trend,
            'success_trend' => $successTrend,
            'avg_duration' => $formattedDuration,
            'resource_usage' => $resourceUsage['hours'] . ' hrs',
            'resource_percentage' => $resourceUsage['percentage'],
        ];
    }

    /**
     * Format a duration in seconds to a human-readable string.
     */
    private function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm ' . $remainingSeconds . 's';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    /**
     * Calculate resource usage for the current user's team.
     */
    private function calculateResourceUsage($user)
    {
        $team = $user->teams()->first();

        if (!$team) {
            return [
                'hours' => 0,
                'percentage' => 0
            ];
        }

        $subscription = $team->subscriptions()
            ->where('is_active', true)
            ->first();

        if (!$subscription) {
            return [
                'hours' => 0,
                'percentage' => 0
            ];
        }

        // In a real application, you would calculate the actual usage from your database
        // For this example, we'll just use some placeholder values
        $containerHours = 10;
        $maxHours = $subscription->max_containers * 24;
        $percentage = $maxHours > 0 ? round(($containerHours / $maxHours) * 100) : 0;

        return [
            'hours' => $containerHours,
            'percentage' => $percentage
        ];
    }
}
