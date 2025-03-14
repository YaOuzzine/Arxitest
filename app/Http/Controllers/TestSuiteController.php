<?php

namespace App\Http\Controllers;

use App\Models\TestSuite;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TestSuiteController extends Controller
{
    /**
     * Display a listing of the test suites.
     */
    public function index(Request $request)
    {
        // Get user's teams
        $teamIds = Auth::user()->teams()->pluck('teams.id');

        $query = TestSuite::whereHas('project', function ($query) use ($teamIds) {
            $query->whereIn('team_id', $teamIds);
        })->with(['project', 'testScripts']);

        // Filter by project if provided
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        // Search by name or description
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $testSuites = $query->orderBy('updated_at', 'desc')->paginate(10);

        return view('test-suites.index', compact('testSuites'));
    }

    /**
     * Show the form for creating a new test suite.
     */
    public function create(Request $request)
    {
        // Load projects the user has access to
        $teamIds = Auth::user()->teams()->pluck('teams.id');
        $projects = Project::whereIn('team_id', $teamIds)->get();

        // If project_id is provided in the query string, pass it to the view
        $projectId = $request->query('project_id');

        return view('test-suites.create', compact('projects', 'projectId'));
    }

    /**
     * Store a newly created test suite in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify user has access to the project
        $teamIds = Auth::user()->teams()->pluck('teams.id');
        $project = Project::where('id', $request->project_id)
            ->whereIn('team_id', $teamIds)
            ->first();

        if (!$project) {
            return redirect()->back()
                ->with('error', 'You do not have access to this project')
                ->withInput();
        }

        // Process settings
        $settings = $request->settings ?? [];

        // Set boolean values for checkboxes
        $checkboxSettings = [
            'parallel_execution',
            'data_driven',
            'retry_on_failure',
            'capture_screenshots'
        ];

        foreach ($checkboxSettings as $checkbox) {
            $settings[$checkbox] = isset($settings[$checkbox]) && $settings[$checkbox] == '1';
        }

        // Create the test suite
        $testSuite = TestSuite::create([
            'project_id' => $request->project_id,
            'name' => $request->name,
            'description' => $request->description,
            'settings' => $settings,
        ]);

        return redirect()->route('test-suites.show', $testSuite->id)
            ->with('success', 'Test suite created successfully!');
    }

    /**
     * Display the specified test suite.
     */
    public function show(TestSuite $testSuite)
    {
        // Check if user has access to the test suite
        if (!$this->checkTestSuiteAccess($testSuite)) {
            abort(403, 'Unauthorized access to this test suite.');
        }

        // Eager load relationships
        $testSuite->load(['project', 'testScripts', 'testScripts.jiraStory', 'testScripts.creator']);

        return view('test-suites.show', compact('testSuite'));
    }

    /**
     * Show the form for editing the specified test suite.
     */
    public function edit(TestSuite $testSuite)
    {
        // Check if user has access to the test suite
        if (!$this->checkTestSuiteAccess($testSuite)) {
            abort(403, 'Unauthorized access to this test suite.');
        }

        // Eager load necessary relationships
        $testSuite->load(['project', 'testScripts']);

        return view('test-suites.edit', compact('testSuite'));
    }

    /**
     * Update the specified test suite in storage.
     */
    public function update(Request $request, TestSuite $testSuite)
    {
        // Check if user has access to the test suite
        if (!$this->checkTestSuiteAccess($testSuite)) {
            abort(403, 'Unauthorized access to this test suite.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Process settings to ensure checkbox values are properly handled
        $settings = $request->settings ?? [];

        // Set boolean values for checkboxes
        $checkboxSettings = [
            'parallel_execution',
            'data_driven',
            'retry_on_failure',
            'capture_screenshots'
        ];

        foreach ($checkboxSettings as $checkbox) {
            $settings[$checkbox] = isset($settings[$checkbox]) && $settings[$checkbox] == '1';
        }

        // Update the test suite
        $testSuite->update([
            'name' => $request->name,
            'description' => $request->description,
            'settings' => $settings,
        ]);

        return redirect()->route('test-suites.show', $testSuite->id)
            ->with('success', 'Test suite updated successfully!');
    }

    /**
     * Remove the specified test suite from storage.
     */
    public function destroy(Request $request, TestSuite $testSuite)
    {
        // Check if user has access to the test suite
        if (!$this->checkTestSuiteAccess($testSuite)) {
            abort(403, 'Unauthorized access to this test suite.');
        }

        // Check if test suite has scripts
        if ($testSuite->testScripts()->count() > 0) {
            // Confirm deletion with user
            if (!$request->has('confirm_delete')) {
                return redirect()->back()
                    ->with('warning', 'This test suite contains test scripts. Are you sure you want to delete it?')
                    ->with('confirm_delete_url', route('test-suites.destroy', $testSuite->id));
            }
        }

        // Get the project ID for redirection after deletion
        $projectId = $testSuite->project_id;

        // Delete the test suite (this will cascade to test scripts due to foreign key constraint)
        $testSuite->delete();

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Test suite and all associated scripts deleted successfully!');
    }

    /**
     * Export the test suite and its scripts.
     */
    public function export(TestSuite $testSuite)
    {
        // Check if user has access to the test suite
        if (!$this->checkTestSuiteAccess($testSuite)) {
            abort(403, 'Unauthorized access to this test suite.');
        }

        // Load the test suite with its scripts and data
        $testSuite->load(['testScripts', 'testScripts.testVersions']);

        // Create an export array
        $exportData = [
            'suite' => [
                'name' => $testSuite->name,
                'description' => $testSuite->description,
                'settings' => $testSuite->settings,
                'created_at' => $testSuite->created_at,
                'updated_at' => $testSuite->updated_at,
            ],
            'scripts' => []
        ];

        // Add all scripts to the export
        foreach ($testSuite->testScripts as $script) {
            $exportData['scripts'][] = [
                'name' => $script->name,
                'framework_type' => $script->framework_type,
                'script_content' => $script->script_content,
                'jira_key' => $script->jiraStory ? $script->jiraStory->jira_key : null,
                'metadata' => $script->metadata
            ];
        }

        // Convert to JSON
        $jsonData = json_encode($exportData, JSON_PRETTY_PRINT);

        // Generate a filename
        $filename = Str::slug($testSuite->name) . '_export_' . date('Y-m-d') . '.json';

        // Return as a download
        return response()->streamDownload(function() use ($jsonData) {
            echo $jsonData;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Show form for importing a test suite.
     */
    public function importForm(Request $request)
    {
        // Load projects the user has access to
        $teamIds = Auth::user()->teams()->pluck('teams.id');
        $projects = Project::whereIn('team_id', $teamIds)->get();

        // If project_id is provided in the query string, pass it to the view
        $projectId = $request->query('project_id');

        return view('test-suites.import', compact('projects', 'projectId'));
    }

    /**
     * Import a test suite from a JSON file.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:json|max:2048',
            'project_id' => 'required|exists:projects,id',
            'suite_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify user has access to the project
        $teamIds = Auth::user()->teams()->pluck('teams.id');
        $project = Project::where('id', $request->project_id)
            ->whereIn('team_id', $teamIds)
            ->first();

        if (!$project) {
            return redirect()->back()
                ->with('error', 'You do not have access to this project')
                ->withInput();
        }

        try {
            // Read the import file
            $importFile = $request->file('import_file');
            $importData = json_decode(file_get_contents($importFile->path()), true);

            if (!isset($importData['suite']) || !isset($importData['scripts'])) {
                return redirect()->back()
                    ->with('error', 'Invalid import file format')
                    ->withInput();
            }

            // Create the new test suite
            $testSuite = TestSuite::create([
                'project_id' => $request->project_id,
                'name' => $request->suite_name,
                'description' => $importData['suite']['description'] ?? null,
                'settings' => $importData['suite']['settings'] ?? [],
            ]);

            // Import the scripts
            $scriptCount = 0;
            foreach ($importData['scripts'] as $scriptData) {
                // Create the test script
                $testScript = $testSuite->testScripts()->create([
                    'creator_id' => Auth::id(),
                    'name' => $scriptData['name'],
                    'framework_type' => $scriptData['framework_type'],
                    'script_content' => $scriptData['script_content'],
                    'metadata' => $scriptData['metadata'] ?? [],
                ]);

                // Create initial version
                $testScript->testVersions()->create([
                    'version_hash' => md5($scriptData['script_content'] . time()),
                    'script_content' => $scriptData['script_content'],
                    'changes' => ['initial_version' => true],
                    'created_at' => now(),
                ]);

                $scriptCount++;
            }

            return redirect()->route('test-suites.show', $testSuite->id)
                ->with('success', "Test suite imported successfully with {$scriptCount} scripts!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing test suite: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Run all tests in the suite.
     */
    public function runAllTests(Request $request, TestSuite $testSuite)
    {
        // Check if user has access to the test suite
        if (!$this->checkTestSuiteAccess($testSuite)) {
            abort(403, 'Unauthorized access to this test suite.');
        }

        $validator = Validator::make($request->all(), [
            'environment_id' => 'required|exists:environments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Load all test scripts in the suite
        $testScripts = $testSuite->testScripts;

        if ($testScripts->isEmpty()) {
            return redirect()->back()
                ->with('warning', 'This test suite has no scripts to run');
        }

        // Get the running status ID
        $runningStatus = \App\Models\ExecutionStatus::where('name', 'Running')->first();

        if (!$runningStatus) {
            return redirect()->back()
                ->with('error', 'Could not find "Running" execution status');
        }

        // Create test executions for each script
        $executionCount = 0;

        foreach ($testScripts as $script) {
            // Create the test execution
            $execution = \App\Models\TestExecution::create([
                'script_id' => $script->id,
                'initiator_id' => Auth::id(),
                'environment_id' => $request->environment_id,
                'status_id' => $runningStatus->id,
                'start_time' => now(),
            ]);

            // Create a container for this execution
            \App\Models\Container::create([
                'execution_id' => $execution->id,
                'container_id' => 'container-' . \Illuminate\Support\Str::random(10),
                'status' => 'running',
                'configuration' => [
                    'resources' => [
                        'cpu' => '1 Core',
                        'memory' => '2GB'
                    ],
                    'parallel' => $testSuite->settings['parallel_execution'] ?? false,
                ],
                'start_time' => now(),
            ]);

            $executionCount++;
        }

        return redirect()->route('test-executions.index')
            ->with('success', "Started {$executionCount} test executions from the suite!");
    }

    /**
     * Check if the authenticated user has access to the test suite
     */
    private function checkTestSuiteAccess(TestSuite $testSuite)
    {
        $user = Auth::user();
        $teamIds = $user->teams()->pluck('teams.id');

        return Project::where('id', $testSuite->project_id)
            ->whereIn('team_id', $teamIds)
            ->exists();
    }
}
