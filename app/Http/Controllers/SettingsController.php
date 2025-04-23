<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Show the main settings dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Please select a team first.');
        }

        // Load team information
        $team = $user->teams()->find($currentTeamId);

        if (!$team) {
            Log::warning('Settings access failed: Session team invalid or user not member.', [
                'user_id' => $user->id,
                'session_current_team' => $currentTeamId
            ]);
            session()->forget('current_team');
            return redirect()->route('dashboard.select-team')->with('error', 'Invalid team selection. Please re-select.');
        }

        // Load stats for the settings dashboard
        $stats = $this->getTeamStats($team);

        // Get user preferences from session or database
        $preferences = $this->getUserPreferences($user, $team);

        return view('dashboard.settings.index', [
            'user' => $user,
            'team' => $team,
            'stats' => $stats,
            'preferences' => $preferences,
        ]);
    }

    /**
 * Update application settings.
 */
public function updateAppSettings(Request $request)
{
    $user = Auth::user();
    $teamId = session('current_team');

    $validated = $request->validate([
        'theme' => 'required|in:light,dark,system',
        'aiEnabled' => 'required|boolean',
        'defaultTestFramework' => 'required|string',
        'defaultTestPriority' => 'required|in:low,medium,high',
        'defaultExecutionMode' => 'required|in:sequential,parallel',
    ]);

    // Store in database
    foreach ($validated as $key => $value) {
        UserSetting::getOrSet($user->id, $key, $value, $teamId);
    }

    // Apply theme immediately via cookie
    if ($validated['theme'] != 'system') {
        cookie()->queue('theme', $validated['theme'], 60*24*365); // 1 year
    } else {
        cookie()->queue(cookie()->forget('theme'));
    }

    return redirect()->route('dashboard.settings.index')
        ->with('success', 'Application settings updated successfully');
}

    /**
 * Update test execution settings.
 */
public function updateTestExecutionSettings(Request $request)
{
    $user = Auth::user();
    $teamId = session('current_team');

    $validated = $request->validate([
        'containerTimeout' => 'required|integer|min:60|max:3600',
        'defaultPageTimeout' => 'required|integer|min:5|max:300',
        'screenshotCapture' => 'required|in:always,failures-only,never',
        'defaultEnvironment' => 'required|string',
    ]);

    // Store in database
    foreach ($validated as $key => $value) {
        UserSetting::getOrSet($user->id, $key, $value, $teamId);
    }

    return redirect()->route('dashboard.settings.index')
        ->with('success', 'Test execution settings updated successfully');
}

    /**
     * Get team statistics for the settings dashboard.
     */
    private function getTeamStats($team)
    {
        $stats = new \stdClass();

        // Get project count
        $stats->projectCount = $team->projects()->count();

        // Get test suites count using relationship
        $stats->testSuiteCount = DB::table('test_suites')
            ->join('projects', 'test_suites.project_id', '=', 'projects.id')
            ->where('projects.team_id', $team->id)
            ->count();

        // Get test cases count
        $stats->testCaseCount = DB::table('test_cases')
            ->join('test_suites', 'test_cases.suite_id', '=', 'test_suites.id')
            ->join('projects', 'test_suites.project_id', '=', 'projects.id')
            ->where('projects.team_id', $team->id)
            ->count();

        // Get executions count
        $stats->executionsCount = DB::table('test_executions')
            ->join('test_scripts', 'test_executions.script_id', '=', 'test_scripts.id')
            ->join('test_cases', 'test_scripts.test_case_id', '=', 'test_cases.id')
            ->join('test_suites', 'test_cases.suite_id', '=', 'test_suites.id')
            ->join('projects', 'test_suites.project_id', '=', 'projects.id')
            ->where('projects.team_id', $team->id)
            ->count();

        // Get team user count
        $stats->teamUserCount = $team->users()->count();

        return $stats;
    }

    /**
 * Get user preferences from database or use defaults.
 */
private function getUserPreferences($user, $team)
{
    // Get all settings for this user and team
    $storedSettings = UserSetting::getAllForUser($user->id, $team->id);

    // Define defaults
    $defaults = [
        'defaultTestFramework' => 'selenium-python',
        'defaultExecutionMode' => 'sequential',
        'defaultEnvironment' => 'development',
        'containerTimeout' => 600,
        'theme' => 'system',
        'aiEnabled' => true,
        'screenshotCapture' => 'failures-only',
        'defaultPageTimeout' => 30,
        'defaultTestPriority' => 'medium',
    ];

    // Merge stored settings with defaults
    $preferences = array_merge($defaults, $storedSettings);

    return $preferences;
}
}
