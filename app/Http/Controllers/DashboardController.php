<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\TestExecution;
use App\Models\TestSuite;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection; // Import Collection
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass; // Use stdClass for stats

class DashboardController extends Controller
{
    public function showDashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $team = $this->getCurrentTeam($request);

        // --- Calculate Stats ---
        $stats = new stdClass();
        $projectIds = $team->projects->pluck('id');

        $stats->projectCount = $team->projects->count();
        $stats->suiteCount = $team->projects->sum('test_suites_count');
        $stats->caseCount = $team->projects->sum('test_cases_count');

        // --- Fetch Recent Executions for the Team ---
        $recentExecutions = collect();
        if ($projectIds->isNotEmpty()) {
            $scriptIds = DB::table('test_scripts as ts')
                ->join('test_cases as tc', 'ts.test_case_id', '=', 'tc.id')
                ->join('test_suites as tsuite', 'tc.suite_id', '=', 'tsuite.id')
                ->whereIn('tsuite.project_id', $projectIds)
                ->pluck('ts.id');

            if ($scriptIds->isNotEmpty()) {
                $recentExecutions = TestExecution::query()
                    ->whereIn('script_id', $scriptIds)
                    ->with([
                        'testScript:id,name,test_case_id',
                        'testScript.testCase:id,suite_id',
                        'testScript.testCase.testSuite:id,project_id,name',
                        'testScript.testCase.testSuite.project:id,name',
                        'initiator:id,name', // User name is already here
                        'status:id,name',
                        'environment:id,name'
                    ])
                    ->select('id', 'script_id', 'initiator_id', 'environment_id', 'status_id', 'start_time', 'end_time', 'created_at')
                    ->orderBy('start_time', 'desc')
                    ->limit(5)
                    ->get();
            }
        }

         // --- Calculate Execution History for Chart (Last 7 Days) ---
        $executionHistory = $this->calculateExecutionHistory($projectIds);
        $stats->executionHistory = $executionHistory;

        // --- Prepare Recent Projects List ---
        $recentProjects = $team->projects;

        // --- Pass Data to View ---
        return view('dashboard.index', compact(
            'user',
            'team',
            'stats',
            'recentExecutions',
            'recentProjects'
        ));
    }

    /**
     * Calculate execution history for the last 7 days for the given project IDs.
     *
     * @param Collection $projectIds
     * @return array
     */
    private function calculateExecutionHistory(Collection $projectIds): array
    {
        if ($projectIds->isEmpty()) {
            return [];
        }

        $historyLimit = now()->subDays(6)->startOfDay(); // Get data for the last 7 days (including today)

        $scriptIds = DB::table('test_scripts as ts')
            ->join('test_cases as tc', 'ts.test_case_id', '=', 'tc.id')
            ->join('test_suites as tsuite', 'tc.suite_id', '=', 'tsuite.id')
            ->whereIn('tsuite.project_id', $projectIds)
            ->pluck('ts.id');

        if ($scriptIds->isEmpty()) {
            return [];
        }

        // Fetch relevant executions within the date range
        $executions = TestExecution::query()
            ->whereIn('script_id', $scriptIds)
            ->whereNotNull('start_time')
            ->where('start_time', '>=', $historyLimit)
            ->with('status:id,name') // Eager load only status name needed for grouping
            ->select('id', 'status_id', 'start_time') // Select only needed columns
            ->get();

        // Define statuses for passed/failed categories
        $passedStatuses = ['completed', 'passed']; // Case-insensitive comparison below
        $failedStatuses = ['failed', 'aborted', 'timeout', 'error'];

        // Group executions by date and calculate passed/failed counts
        $dailyHistory = $executions
            ->groupBy(function ($exec) {
                // Group by date part only
                return Carbon::parse($exec->start_time)->format('Y-m-d');
            })
            ->map(function ($dayExecutions) use ($passedStatuses, $failedStatuses) {
                // Count passed and failed within each day's group
                return [
                    'passed' => $dayExecutions->filter(fn($exec) => in_array(strtolower($exec->status?->name ?? ''), $passedStatuses))->count(),
                    'failed' => $dayExecutions->filter(fn($exec) => in_array(strtolower($exec->status?->name ?? ''), $failedStatuses))->count(),
                ];
            })
            ->sortKeys(); // Ensure dates are sorted chronologically

        // Prepare chart data, ensuring all last 7 days are present (even if no executions)
        $chartHistory = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartHistory[$date] = $dailyHistory->get($date, ['passed' => 0, 'failed' => 0]); // Default to 0 if no data for a day
        }

        return $chartHistory;
    }

    public function showSelectTeam()
    {
        $user = auth('web')->user();
        $teams = $user->teams()->with('users')->get();

        // Get pending invitations for the current user
        $pendingInvitations = TeamInvitation::where('email', $user->email)
            ->where('expires_at', '>', now())
            ->with('team')
            ->get();

        return view('dashboard.select-team', [
            'teams' => $teams,
            'user' => $user,
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function setCurrentTeam(Request $request){
        $team_id = $request->input('team_id');

        try{
            // Ensure the user actually belongs to the team they are trying to select
            $team = auth('web')->user()->teams()->findOrFail($team_id);
        }
        catch (ModelNotFoundException $e){
            return back()->withErrors([
                'team_id' => 'You do not belong to that team or the team does not exist.'
            ])->withInput(); // Add withInput()
        }

        session(['current_team' => $team_id]);

        // Regenerate session ID for security upon changing team context
        $request->session()->regenerate();

        return redirect()->intended('dashboard'); // Redirect to dashboard after setting team
    }


}
