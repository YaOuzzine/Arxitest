<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestScript;
use App\Models\TestSuite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class TestScriptController extends Controller
{
    /**
     * Authorization check (temporarily disabled like other controllers)
     */
    private function authorizeAccess($project): void
    {
        Log::warning('AUTHORIZATION CHECK IS TEMPORARILY DISABLED in TestScriptController@authorizeAccess');
    }

    /**
     * Display a listing of test scripts for a test case.
     */
    public function index(Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            abort(404, 'Test case not found in this project.');
        }

        $testScripts = $test_case->testScripts()->orderBy('created_at', 'desc')->get();

        return view('dashboard.test-scripts.index', [
            'project' => $project,
            'testCase' => $test_case,
            'testSuite' => $suite,
            'testScripts' => $testScripts
        ]);
    }

    /**
     * Store a newly created test script.
     */
    public function store(Request $request, Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            abort(404, 'Test case not found in this project.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'framework_type' => 'required|string|in:selenium-python,cypress,other',
            'script_content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $testScript = new TestScript();
        $testScript->test_case_id = $test_case->id;
        $testScript->creator_id = Auth::id();
        $testScript->name = $request->input('name');
        $testScript->framework_type = $request->input('framework_type');
        $testScript->script_content = $request->input('script_content');
        $testScript->metadata = [
            'created_through' => 'manual',
            'source' => 'user'
        ];
        $testScript->save();

        return redirect()->route('dashboard.projects.test-cases.show', [
            'project' => $project->id,
            'test_case' => $test_case->id
        ])->with('success', 'Test script created successfully.');
    }

    /**
     * Generate a test script using AI.
     */
    public function generateWithAI(Request $request, Project $project, TestCase $test_case)
    {
        $this->authorizeAccess($project);

        $validator = Validator::make($request->all(), [
            'framework_type' => 'required|string|in:selenium-python,cypress,other',
            'prompt' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Test case not found in this project.'
            ], 404);
        }

        $framework = $request->input('framework_type');

        // Create context for the AI
        $steps = is_array($test_case->steps) ? $test_case->steps : json_decode($test_case->steps, true);
        $stepsText = is_array($steps) ? implode("\n", array_map(fn($i, $step) => ($i+1) . ". $step", array_keys($steps), $steps)) : $test_case->steps;

        $userPrompt = $request->input('prompt') ?: "Generate a test script based on the test case";

        $systemPrompt = <<<PROMPT
You are an AI assistant that specializes in generating test automation scripts. You'll create a test script based on the provided test case information.

Test Case Details:
- Title: {$test_case->title}
- Description: {$test_case->description}
- Steps:
{$stepsText}
- Expected Results: {$test_case->expected_results}

Create a complete, working test script using the {$framework} framework. Do not abbreviate or omit parts of the code - generate a complete test script that could be executed.

Follow these specific guidelines:
1. For selenium-python:
   - Use Selenium WebDriver with Python
   - Include proper imports, setup, teardown
   - Use best practices like explicit waits
   - Structure as a proper Python test using unittest or pytest

2. For cypress:
   - Use Cypress JavaScript syntax
   - Include proper imports, before/after hooks
   - Follow Cypress best practices
   - Structure as a proper Cypress test

Your response should be ONLY the code for the test script, without explanations, markdown formatting, or code block markers.
PROMPT;

        try {
            $apiKey = env('OPENAI_API_KEY', config('services.openai.key'));
            $model = env('OPENAI_MODEL', config('services.openai.model', 'gpt-4o'));

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI service is not configured. Please check the API key.'
                ], 500);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI generation failed. Error: ' . $response->status()
                ], 500);
            }

            $scriptContent = $response->json('choices.0.message.content');

            // Clean up any possible markdown code blocks
            $scriptContent = trim(preg_replace('/^```[\w]*\n|```$/m', '', $scriptContent));

            $scriptName = $test_case->title . ' - ' . ucfirst($framework) . ' Script';

            // Create and save the script
            $testScript = new TestScript();
            $testScript->test_case_id = $test_case->id;
            $testScript->creator_id = Auth::id();
            $testScript->name = $scriptName;
            $testScript->framework_type = $framework;
            $testScript->script_content = $scriptContent;
            $testScript->metadata = [
                'created_through' => 'ai',
                'source' => 'openai',
                'model' => $model,
                'prompt' => $userPrompt
            ];
            $testScript->save();

            return response()->json([
                'success' => true,
                'message' => 'Test script generated successfully',
                'script' => [
                    'id' => $testScript->id,
                    'name' => $testScript->name,
                    'content' => $testScript->script_content,
                    'framework_type' => $testScript->framework_type
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating test script with AI: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the test script: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified test script.
     */
    public function show(Project $project, TestCase $test_case, TestScript $test_script)
    {
        $this->authorizeAccess($project);

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            abort(404, 'Test case not found in this project.');
        }

        // Ensure test script belongs to this test case
        if ($test_script->test_case_id !== $test_case->id) {
            abort(404, 'Test script not found in this test case.');
        }

        return view('dashboard.test-scripts.show', [
            'project' => $project,
            'testCase' => $test_case,
            'testSuite' => $suite,
            'testScript' => $test_script
        ]);
    }

    /**
     * Remove the specified test script.
     */
    public function destroy(Project $project, TestCase $test_case, TestScript $test_script)
    {
        $this->authorizeAccess($project);

        // Ensure test case belongs to a suite in this project
        $suite = $test_case->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            abort(404, 'Test case not found in this project.');
        }

        // Ensure test script belongs to this test case
        if ($test_script->test_case_id !== $test_case->id) {
            abort(404, 'Test script not found in this test case.');
        }

        $scriptName = $test_script->name;
        $test_script->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Test script \"$scriptName\" deleted successfully."
            ]);
        }

        return redirect()->route('dashboard.projects.test-cases.show', [
            'project' => $project->id,
            'test_case' => $test_case->id
        ])->with('success', "Test script \"$scriptName\" deleted successfully.");
    }
}
