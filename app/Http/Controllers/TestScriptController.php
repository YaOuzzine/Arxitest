<?php

namespace App\Http\Controllers;

use App\Models\TestScript;
use App\Models\TestSuite;
use App\Models\JiraStory;
use App\Models\TestVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TestScriptController extends Controller
{
    /**
     * Display a listing of the test scripts.
     */
    public function index(Request $request)
    {
        $query = TestScript::with(['testSuite', 'creator', 'jiraStory']);

        // Filter by test suite if provided
        if ($request->has('suite_id')) {
            $query->where('suite_id', $request->suite_id);
        }

        // Filter by framework type if provided
        if ($request->has('framework_type')) {
            $query->where('framework_type', $request->framework_type);
        }

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $testScripts = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('test-scripts.index', compact('testScripts'));
    }

    /**
     * Show the form for creating a new test script.
     */
    public function create()
    {
        $testSuites = TestSuite::all();
        $jiraStories = JiraStory::all();
        $frameworkTypes = ['selenium_python', 'cypress']; // Available framework types

        return view('test-scripts.create', compact('testSuites', 'jiraStories', 'frameworkTypes'));
    }

    /**
     * Store a newly created test script in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suite_id' => 'required|exists:test_suites,id',
            'name' => 'required|string|max:255',
            'framework_type' => 'required|string|in:selenium_python,cypress',
            'script_content' => 'required|string',
            'jira_story_id' => 'nullable|exists:jira_stories,id',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create the test script
        $testScript = TestScript::create([
            'suite_id' => $request->suite_id,
            'creator_id' => Auth::id(),
            'name' => $request->name,
            'framework_type' => $request->framework_type,
            'script_content' => $request->script_content,
            'jira_story_id' => $request->jira_story_id,
            'metadata' => $request->metadata ?? [],
        ]);

        // Create the initial version record
        TestVersion::create([
            'script_id' => $testScript->id,
            'version_hash' => md5($request->script_content . time()),
            'script_content' => $request->script_content,
            'changes' => ['initial_version' => true],
            'created_at' => now(),
        ]);

        return redirect()->route('test-scripts.show', $testScript->id)
            ->with('success', 'Test script created successfully!');
    }

    /**
     * Display the specified test script.
     */
    public function show(TestScript $testScript)
    {
        $testScript->load(['testSuite', 'creator', 'jiraStory', 'testVersions']);

        // Get the latest execution if available
        $latestExecution = $testScript->testExecutions()
            ->with('executionStatus')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('test-scripts.show', compact('testScript', 'latestExecution'));
    }

    /**
     * Show the form for editing the specified test script.
     */
    public function edit(TestScript $testScript)
    {
        $testSuites = TestSuite::all();
        $jiraStories = JiraStory::all();
        $frameworkTypes = ['selenium_python', 'cypress']; // Available framework types

        return view('test-scripts.edit', compact('testScript', 'testSuites', 'jiraStories', 'frameworkTypes'));
    }

    /**
     * Update the specified test script in storage.
     */
    public function update(Request $request, TestScript $testScript)
    {
        $validator = Validator::make($request->all(), [
            'suite_id' => 'required|exists:test_suites,id',
            'name' => 'required|string|max:255',
            'framework_type' => 'required|string|in:selenium_python,cypress',
            'script_content' => 'required|string',
            'jira_story_id' => 'nullable|exists:jira_stories,id',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if script content has changed
        $contentChanged = $testScript->script_content !== $request->script_content;

        // Update the test script
        $testScript->update([
            'suite_id' => $request->suite_id,
            'name' => $request->name,
            'framework_type' => $request->framework_type,
            'script_content' => $request->script_content,
            'jira_story_id' => $request->jira_story_id,
            'metadata' => $request->metadata ?? [],
        ]);

        // Create a new version record if the content changed
        if ($contentChanged) {
            TestVersion::create([
                'script_id' => $testScript->id,
                'version_hash' => md5($request->script_content . time()),
                'script_content' => $request->script_content,
                'changes' => ['updated_by' => Auth::id(), 'updated_at' => now()],
                'created_at' => now(),
            ]);
        }

        return redirect()->route('test-scripts.show', $testScript->id)
            ->with('success', 'Test script updated successfully!');
    }

    /**
     * Remove the specified test script from storage.
     */
    public function destroy(TestScript $testScript)
    {
        // Check if the test script has executions
        $hasExecutions = $testScript->testExecutions()->exists();

        if ($hasExecutions) {
            return redirect()->back()
                ->with('error', 'Cannot delete a test script that has been executed. Consider archiving it instead.');
        }

        $testScript->delete();

        return redirect()->route('test-scripts.index')
            ->with('success', 'Test script deleted successfully!');
    }

    /**
     * Generate a test script using AI based on Jira story.
     */
    public function generateScript(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jira_story_id' => 'required|exists:jira_stories,id',
            'framework_type' => 'required|string|in:selenium_python,cypress',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the Jira story
        $jiraStory = JiraStory::findOrFail($request->jira_story_id);

        // In a real implementation, this would call your LLAMA model API
        // For now, we'll return a template based on the framework type
        $generatedScript = $this->getScriptTemplate($request->framework_type, $jiraStory);

        return response()->json([
            'success' => true,
            'script_content' => $generatedScript,
            'suggested_name' => 'Test_' . str_replace(' ', '_', $jiraStory->title),
        ]);
    }

    /**
     * Get a script template based on framework type and Jira story.
     */
    private function getScriptTemplate($frameworkType, $jiraStory)
    {
        // This is a placeholder for the actual AI-based generation
        // In a real implementation, you would call your LLAMA model here

        if ($frameworkType === 'selenium_python') {
            return "# Auto-generated test for: {$jiraStory->title} ({$jiraStory->jira_key})
import unittest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class {$this->getClassNameFromTitle($jiraStory->title)}(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Chrome()
        self.driver.maximize_window()
        self.driver.get('https://your-application-url.com')

    def test_{$this->getMethodNameFromTitle($jiraStory->title)}(self):
        # TODO: Implement test steps based on acceptance criteria
        # Description: {$jiraStory->description}

        # Example:
        # login_button = WebDriverWait(self.driver, 10).until(
        #     EC.element_to_be_clickable((By.ID, 'login-button'))
        # )
        # login_button.click()

        self.assertTrue(True)  # Replace with actual assertions

    def tearDown(self):
        self.driver.quit()

if __name__ == '__main__':
    unittest.main()";
        } else if ($frameworkType === 'cypress') {
            return "// Auto-generated test for: {$jiraStory->title} ({$jiraStory->jira_key})
describe('{$jiraStory->title}', () => {
  beforeEach(() => {
    cy.visit('https://your-application-url.com')
  })

  it('should {$this->getMethodNameFromTitle($jiraStory->title)}', () => {
    // TODO: Implement test steps based on acceptance criteria
    // Description: {$jiraStory->description}

    // Example:
    // cy.get('#login-button').click()
    // cy.get('#username').type('testuser')
    // cy.get('#password').type('password')
    // cy.get('#submit').click()
    // cy.url().should('include', '/dashboard')

    expect(true).to.equal(true)  // Replace with actual assertions
  })
})";
        }

        return "// Please select a supported framework type";
    }

    /**
     * Convert a story title to a valid class name.
     */
    private function getClassNameFromTitle($title)
    {
        $className = str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9\s]/', '', $title)));
        return "Test" . $className;
    }

    /**
     * Convert a story title to a valid method name.
     */
    private function getMethodNameFromTitle($title)
    {
        $methodName = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($title));
        $methodName = str_replace(' ', '_', $methodName);
        return $methodName;
    }

    /**
     * Get version history for a test script.
     */
    public function versionHistory(TestScript $testScript)
    {
        $versions = $testScript->testVersions()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('test-scripts.versions', compact('testScript', 'versions'));
    }

    /**
     * Restore a specific version of a test script.
     */
    public function restoreVersion(TestScript $testScript, TestVersion $version)
    {
        // Update the test script with the version's content
        $testScript->update([
            'script_content' => $version->script_content
        ]);

        // Create a new version record to track the restoration
        TestVersion::create([
            'script_id' => $testScript->id,
            'version_hash' => md5($version->script_content . time()),
            'script_content' => $version->script_content,
            'changes' => [
                'restored_from' => $version->id,
                'restored_by' => Auth::id(),
                'restored_at' => now()
            ],
            'created_at' => now(),
        ]);

        return redirect()->route('test-scripts.show', $testScript->id)
            ->with('success', 'Test script restored to previous version successfully!');
    }
}
