<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\JiraStory;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\TestExecution;
use App\Models\TestScript;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // This is causing the error - let's handle auth via routes instead
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's team ID
        $teamId = $user->teams()->first()->id ?? null;

        // Get recent test executions
        $recentExecutions = $this->getRecentExecutions($user->id);

        // Get projects with counts
        $projects = $this->getProjects($teamId);

        // Get execution statistics
        $executionStats = $this->getExecutionStats($user->id);

        // Get recent Jira stories
        $jiraStories = $this->getJiraStories($teamId);

        // Get resource usage statistics
        $resourceStats = $this->getResourceStats($teamId);

        // Get user's subscription
        $subscription = $this->getSubscription($teamId);

        // Get recent activity for summary
        $recentActivity = $this->getRecentActivity($user->id);

        // Check if user has any activity at all
        $hasActivity = $this->hasAnyActivity($user->id);

        return view('home', compact(
            'recentExecutions',
            'projects',
            'executionStats',
            'jiraStories',
            'resourceStats',
            'subscription',
            'recentActivity',
            'hasActivity'
        ));
    }

    /**
     * Get recent test executions for the user
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentExecutions(string $userId)
    {
        return TestExecution::with(['testScript.testSuite', 'executionStatus'])
            ->where('initiator_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get projects with test suite and script counts
     *
     * @param string|null $teamId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getProjects(?string $teamId)
    {
        if (!$teamId) {
            return collect();
        }

        return Project::where('team_id', $teamId)
            ->withCount(['testSuites'])
            ->with(['testSuites' => function($query) {
                $query->withCount('testScripts');
            }])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($project) {
                // Calculate total test scripts across all suites
                $testScriptsCount = $project->testSuites->sum('test_scripts_count');
                $project->test_scripts_count = $testScriptsCount;
                return $project;
            });
    }

    /**
     * Get execution statistics
     *
     * @param string $userId
     * @return array
     */
    private function getExecutionStats(string $userId)
    {
        // Get last 30 days of executions
        $lastMonth = Carbon::now()->subDays(30);

        $executions = TestExecution::join('execution_statuses', 'test_executions.status_id', '=', 'execution_statuses.id')
            ->where('test_executions.initiator_id', $userId)
            ->where('test_executions.created_at', '>=', $lastMonth)
            ->select('execution_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('execution_statuses.name')
            ->get();

        if ($executions->isEmpty()) {
            return [];
        }

        $stats = [
            'passed' => 0,
            'failed' => 0,
            'running' => 0,
            'total' => 0
        ];

        foreach ($executions as $execution) {
            $status = strtolower($execution->name);
            if (array_key_exists($status, $stats)) {
                $stats[$status] = $execution->count;
            }
            $stats['total'] += $execution->count;
        }

        return $stats;
    }

    /**
     * Get recent Jira stories
     *
     * @param string|null $teamId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getJiraStories(?string $teamId)
    {
        if (!$teamId) {
            return collect();
        }

        // Get project IDs for the team
        $projectIds = Project::where('team_id', $teamId)
            ->pluck('id')
            ->toArray();

        // Get test scripts that reference Jira stories for these projects
        $jiraStoryIds = DB::table('test_scripts')
            ->join('test_suites', 'test_scripts.suite_id', '=', 'test_suites.id')
            ->whereIn('test_suites.project_id', $projectIds)
            ->whereNotNull('test_scripts.jira_story_id')
            ->distinct()
            ->pluck('test_scripts.jira_story_id')
            ->toArray();

        // Get the Jira stories
        return JiraStory::whereIn('id', $jiraStoryIds)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();
    }

    /**
     * Get resource usage statistics
     *
     * @param string|null $teamId
     * @return array|null
     */
    private function getResourceStats(?string $teamId)
    {
        if (!$teamId) {
            return null;
        }

        $subscription = $this->getSubscription($teamId);

        if (!$subscription) {
            return null;
        }

        // Get container hours used in the current billing period
        $startDate = $subscription->start_date;
        $endDate = $subscription->end_date ?? Carbon::now();

        $totalContainerSeconds = Container::join('test_executions', 'containers.execution_id', '=', 'test_executions.id')
            ->join('test_scripts', 'test_executions.script_id', '=', 'test_scripts.id')
            ->join('test_suites', 'test_scripts.suite_id', '=', 'test_suites.id')
            ->join('projects', 'test_suites.project_id', '=', 'projects.id')
            ->where('projects.team_id', $teamId)
            ->whereBetween('containers.created_at', [$startDate, $endDate])
            ->whereNotNull('containers.start_time')
            ->whereNotNull('containers.end_time')
            ->sum(DB::raw('EXTRACT(EPOCH FROM (containers.end_time - containers.start_time))'));

        $containerHours = round($totalContainerSeconds / 3600, 2);

        // Calculate storage used (total size of logs and results in GB)
        // This would typically involve querying your storage service or database
        // For the example, we'll use a placeholder calculation based on execution count
        $storageUsedBytes = DB::table('test_executions')
            ->join('test_scripts', 'test_executions.script_id', '=', 'test_scripts.id')
            ->join('test_suites', 'test_scripts.suite_id', '=', 'test_suites.id')
            ->join('projects', 'test_suites.project_id', '=', 'projects.id')
            ->where('projects.team_id', $teamId)
            ->whereNotNull('test_executions.s3_results_key')
            ->count() * 5 * 1024 * 1024; // Assuming average 5MB per result

        $storageUsedGB = round($storageUsedBytes / (1024 * 1024 * 1024), 2);

        // Get max containers from subscription or use default
        $maxContainers = $subscription->max_parallel_runs ?? 5;

        return [
            'container_hours' => $containerHours,
            'container_quota' => $maxContainers * 24 * 30, // Simplified calculation
            'storage_used' => $storageUsedGB . ' GB',
            'storage_used_value' => $storageUsedGB,
            'storage_quota' => '10 GB', // This would come from the subscription
            'storage_quota_value' => 10,
        ];
    }

    /**
     * Get the team's subscription
     *
     * @param string|null $teamId
     * @return \App\Models\Subscription|null
     */
    private function getSubscription(?string $teamId)
    {
        if (!$teamId) {
            return null;
        }

        return Subscription::where('team_id', $teamId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get recent activity for the user
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentActivity(string $userId)
    {
        $lastWeek = Carbon::now()->subDays(7);

        return TestExecution::where('initiator_id', $userId)
            ->where('created_at', '>=', $lastWeek)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if user has any activity
     *
     * @param string $userId
     * @return bool
     */
    private function hasAnyActivity(string $userId)
    {
        // Check if user has any test executions
        $hasExecutions = TestExecution::where('initiator_id', $userId)->exists();

        // Check if user has created any test scripts
        $hasScripts = DB::table('test_scripts')
            ->where('creator_id', $userId)
            ->exists();

        // Check if user's team has any projects
        $teamId = User::find($userId)->teams()->first()->id ?? null;
        $hasProjects = $teamId ? Project::where('team_id', $teamId)->exists() : false;

        return $hasExecutions || $hasScripts || $hasProjects;
    }
}
