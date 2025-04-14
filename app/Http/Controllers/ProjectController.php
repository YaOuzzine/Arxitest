<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use stdClass;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects for the current team.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $currentTeamId = session('current_team');
        $team = Team::findOrFail($currentTeamId);

        // Just get the projects with test suites count
        $projects = $team->projects()
            ->withCount('testSuites')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Now manually calculate test cases count for each project
        $projects->each(function ($project) {
            // Load test suites with test case counts
            $suites = $project->testSuites()->withCount('testCases')->get();

            // Sum up the test case counts
            $project->test_cases_count = $suites->sum('test_cases_count');
        });

        return view('dashboard.projects.index', compact('projects', 'team'));
    }

    /**
     * Show the form for creating a new project.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $currentTeamId = session('current_team');
        $team = Team::findOrFail($currentTeamId);

        return view('dashboard.projects.create', compact('team'));
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $currentTeamId = session('current_team');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $project = new Project();
        $project->name = $request->name;
        $project->description = $request->description;
        $project->team_id = $currentTeamId;
        $project->settings = [
            'default_framework' => $request->input('default_framework', 'selenium-python'),
            'auto_generate_tests' => $request->boolean('auto_generate_tests', false),
        ];
        $project->save();

        return redirect()->route('dashboard.projects')
            ->with('success', 'Project created successfully');
    }

    /**
     * Display the specified project.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $project = Project::with(['testSuites.testCases', 'team'])->findOrFail($id);

        // Check if user belongs to the team that owns this project
        $currentTeamId = session('current_team');
        if ($project->team_id !== $currentTeamId) {
            return redirect()->route('dashboard.projects')
                ->with('error', 'You do not have access to this project');
        }

        // Create stats object
        $stats = new stdClass();

        // Calculate total test cases
        $stats->totalTestCases = $project->testSuites->flatMap->testCases->count();

        // Set default stats values or calculate them from actual data
        $stats->testCasesGrowth = 15; // Sample data, replace with actual calculation
        $stats->passRate = 87; // Sample data, replace with actual calculation
        $stats->lastExecutionTime = '2 days ago'; // Sample data, replace with actual calculation
        $stats->lastExecutionStatus = 'All tests passed'; // Sample data
        $stats->avgExecutionTime = '3m 45s'; // Sample data

        // Mock recent activities data for the view
        $recentActivities = $this->getMockRecentActivities();

        // Mock test executions data
        $testExecutions = $this->getMockTestExecutions();

        return view('dashboard.projects.show', compact('project', 'stats', 'recentActivities', 'testExecutions'));
    }

    /**
     * Show the form for editing the specified project.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);

        // Check if user belongs to the team that owns this project
        $currentTeamId = session('current_team');
        if ($project->team_id !== $currentTeamId) {
            return redirect()->route('dashboard.projects')
                ->with('error', 'You do not have access to this project');
        }

        return view('dashboard.projects.edit', compact('project'));
    }

    /**
     * Update the specified project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Check if user belongs to the team that owns this project
        $currentTeamId = session('current_team');
        if ($project->team_id !== $currentTeamId) {
            return redirect()->route('dashboard.projects')
                ->with('error', 'You do not have access to this project');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $project->name = $request->name;
        $project->description = $request->description;

        // Update settings
        $settings = $project->settings ?? [];
        $settings['default_framework'] = $request->input('default_framework', $settings['default_framework'] ?? 'selenium-python');
        $settings['auto_generate_tests'] = $request->boolean('auto_generate_tests', $settings['auto_generate_tests'] ?? false);
        $project->settings = $settings;

        $project->save();

        return redirect()->route('dashboard.projects.show', $project->id)
            ->with('success', 'Project updated successfully');
    }

    /**
     * Remove the specified project from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        // Check if user belongs to the team that owns this project
        $currentTeamId = session('current_team');
        if ($project->team_id !== $currentTeamId) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this project'
                ], 403);
            }

            return redirect()->route('dashboard.projects')
                ->with('error', 'You do not have access to this project');
        }

        $projectName = $project->name;

        // Delete project
        $project->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Project \"$projectName\" was deleted successfully"
            ]);
        }

        return redirect()->route('dashboard.projects')
            ->with('success', "Project \"$projectName\" was deleted successfully");
    }

    /**
     * Get mock recent activities data for demonstration.
     *
     * @return array
     */
    private function getMockRecentActivities()
    {
        return [
            (object)[
                'id' => 1,
                'user' => (object)[
                    'name' => 'Sarah Johnson',
                    'avatar_url' => 'https://ui-avatars.com/api/?name=Sarah+Johnson&background=random'
                ],
                'description' => 'Created a new test suite <span class="font-medium text-zinc-900 dark:text-white">API Authentication</span>',
                'created_at' => now()->subHours(2)
            ],
            (object)[
                'id' => 2,
                'user' => (object)[
                    'name' => 'Michael Chen',
                    'avatar_url' => 'https://ui-avatars.com/api/?name=Michael+Chen&background=random'
                ],
                'description' => 'Run 12 tests in <span class="font-medium text-zinc-900 dark:text-white">Payment Processing</span> project',
                'created_at' => now()->subDay()
            ],
            (object)[
                'id' => 3,
                'user' => (object)[
                    'name' => 'Alex Williams',
                    'avatar_url' => 'https://ui-avatars.com/api/?name=Alex+Williams&background=random'
                ],
                'description' => 'Added 5 new test cases to <span class="font-medium text-zinc-900 dark:text-white">User Management</span>',
                'created_at' => now()->subDays(2)
            ]
        ];
    }

    /**
     * Get mock test executions data for demonstration.
     *
     * @return array
     */
    private function getMockTestExecutions()
    {
        return [
            (object)[
                'id' => 'EXE-1234',
                'status' => 'passed',
                'environment' => 'Production',
                'testSuite' => (object)['name' => 'User Authentication'],
                'initiator' => (object)[
                    'name' => 'Sarah Johnson',
                    'avatar_url' => 'https://ui-avatars.com/api/?name=Sarah+Johnson&background=random'
                ],
                'created_at' => now()->subHours(5),
                'duration' => '2m 34s'
            ],
            (object)[
                'id' => 'EXE-1233',
                'status' => 'failed',
                'environment' => 'Staging',
                'testSuite' => (object)['name' => 'Payment Processing'],
                'initiator' => (object)[
                    'name' => 'Michael Chen',
                    'avatar_url' => 'https://ui-avatars.com/api/?name=Michael+Chen&background=random'
                ],
                'created_at' => now()->subDay(),
                'duration' => '3m 12s'
            ],
            (object)[
                'id' => 'EXE-1232',
                'status' => 'passed',
                'environment' => 'Development',
                'testSuite' => (object)['name' => 'Registration Flow'],
                'initiator' => (object)[
                    'name' => 'Alex Williams',
                    'avatar_url' => 'https://ui-avatars.com/api/?name=Alex+Williams&background=random'
                ],
                'created_at' => now()->subDays(3),
                'duration' => '1m 45s'
            ]
        ];
    }
}
