<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TestSuite;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreTestSuiteRequest;
use App\Http\Requests\UpdateTestSuiteRequest;
use App\Services\TestSuiteService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Needed for indexAll safety check
use App\Traits\JsonResponse;
use App\Traits\AuthorizeResourceAccess;

class TestSuiteController extends Controller
{
    use AuthorizeResourceAccess, JsonResponse;

    protected TestSuiteService $suites;

    public function __construct(TestSuiteService $suites)
    {
        $this->suites = $suites;
    }

    /**
     * GET /dashboard/api/projects/{project}/test-suites
     */
    public function getJsonForProject(Project $project)
    {
        $suites = $project
            ->testSuites()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);

        return response()->json([
            'success'     => true,
            'test_suites' => $suites,
        ]);
    }

    // --- indexAll ---
    public function indexAll(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        // Get project IDs the current user actually belongs to within this team
        $userProjectIds = Auth::user()->teams()
            ->where('teams.id', $currentTeamId)
            ->first()?->projects()->pluck('id'); // Use optional chaining

        $projectsForFilter = Project::whereIn('id', $userProjectIds)->orderBy('name')->get(['id', 'name']);

        $query = TestSuite::query()
            ->whereIn('project_id', $userProjectIds)
            ->with(['project:id,name'])->withCount('testCases') // Removed testCases load here for performance
            ->orderBy('updated_at', 'desc');

        $filterProjectId = $request->input('project_id');
        if ($filterProjectId && $projectsForFilter->contains('id', $filterProjectId)) {
            $query->where('project_id', $filterProjectId);
        }

        $testSuites = $query->get();

        return view('dashboard.test-suites.index', [
            'testSuites' => $testSuites,
            'projects' => $projectsForFilter,
            'team' => $team
        ]);
    }

    // --- index ---
    public function index(Project $project)
    {
        $this->authorizeAccess($project);
        $testSuites = $project->testSuites()
            ->withCount('testCases')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dashboard.test-suites.index', compact('project', 'testSuites'));
    }

    // --- create ---
    public function create(Project $project)
    {
        $this->authorizeAccess($project);
        return view('dashboard.test-suites.create', compact('project'));
    }

    // --- store ---
    public function store(StoreTestSuiteRequest $request, Project $project)
    {
        $this->authorizeAccess($project);
        $suite = $this->suites->create($project, $request->validated());

        return redirect()
            ->route('dashboard.projects.test-suites.index', $project->id)
            ->with('success', 'Test Suite "' . $suite->name . '" created.');
    }

    // --- show ---
    public function show(Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);
        $test_suite->loadMissing('testCases');

        return view('dashboard.test-suites.show', [
            'project'   => $project,
            'testSuite' => $test_suite,
        ]);
    }

    // --- edit ---
    public function edit(Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);
        return view('dashboard.test-suites.edit', [
            'project'   => $project,
            'testSuite' => $test_suite,
        ]);
    }

    // --- update ---
    public function update(UpdateTestSuiteRequest $request, Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);
        $suite = $this->suites->update($test_suite, $request->validated());

        return redirect()
            ->route('dashboard.projects.test-suites.show', [$project->id, $suite->id])
            ->with('success', 'Test Suite updated.');
    }

    // --- destroy ---
    public function destroy(Project $project, TestSuite $test_suite)
    {
        $this->authorizeAccess($project);
        $name = $test_suite->name;
        $this->suites->delete($test_suite);

        if (request()->expectsJson()) {
            return $this->successResponse([], "Suite \"$name\" deleted.");
        }

        return redirect()
            ->route('dashboard.projects.test-suites.index', $project->id)
            ->with('success', "Suite \"$name\" deleted.");
    }

    // --- generateWithAI ---
    /**
     * Generate Test Suite details using Deepseek AI.
     * (Called via AJAX from the create form)
     *
     * @param Request $request
     * @param Project $project Passed via Route Model Binding
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateWithAI(Request $request, Project $project)
    {
        $this->authorizeAccess($project);

        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|min:20|max:2000', // Adjust max length as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $apiKey = config('services.deepseek.key');
        $apiUrl = config('services.deepseek.chat_url');

        if (!$apiKey) {
            Log::error('Deepseek API Key is not configured.');
            return response()->json(['success' => false, 'message' => 'AI service is not configured. Please contact support.'], 500);
        }

        $userPrompt = $request->input('prompt');

        // Construct a detailed prompt for the AI
        $systemPrompt = <<<PROMPT
You are an AI assistant designed to generate Test Suite details in a specific JSON format based on user requirements.
The user will provide requirements for a software feature or component.
Your task is to generate a JSON object containing ONLY the following keys: "name", "description", and "settings".
- "name": A concise, descriptive name for the Test Suite (max 100 chars).
- "description": A brief summary of what the Test Suite covers (max 255 chars).
- "settings": A JSON object with AT LEAST the key "default_priority" set to one of "low", "medium", or "high". You can optionally add other relevant settings like "execution_mode" ("sequential" or "parallel") if implied by the requirements.

Example Input Requirements:
"Create tests for user login functionality. Include positive cases with valid credentials, negative cases with invalid passwords, password recovery link check, and remember me functionality."

Example Output JSON:
{
  "name": "User Login and Authentication",
  "description": "Covers login scenarios including valid/invalid credentials and password recovery.",
  "settings": {
    "default_priority": "high",
    "execution_mode": "sequential"
  }
}

Ensure the output is **strictly** a single JSON object with no extra text, explanations, or markdown formatting.
PROMPT;


        try {
            $response = Http::withToken($apiKey)
                ->timeout(60) // Set a timeout (e.g., 60 seconds)
                ->post($apiUrl, [
                    'model' => 'deepseek-chat', // Or the specific model you want to use
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.6, // Adjust creativity
                    'max_tokens' => 300,  // Limit response size
                    'response_format' => ['type' => 'json_object'] // Request JSON output if API supports it
                ]);

            if ($response->failed()) {
                Log::error('Deepseek API Error: ' . $response->status() . ' - ' . $response->body());
                return response()->json(['success' => false, 'message' => 'AI generation failed. Error: ' . $response->status()], 500);
            }

            $aiContent = $response->json('choices.0.message.content');

            // Attempt to clean and parse the JSON
            // Remove potential markdown fences and trim whitespace
            $jsonString = trim(str_replace(['```json', '```'], '', $aiContent));
            $generatedData = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($generatedData)) {
                Log::error('Deepseek API returned invalid JSON: ' . $jsonString . ' | Original: ' . $aiContent);
                return response()->json(['success' => false, 'message' => 'AI returned an invalid format. Please try again or refine your prompt.'], 500);
            }

            // Basic validation of the returned structure
            if (!isset($generatedData['name']) || !isset($generatedData['description']) || !isset($generatedData['settings']['default_priority'])) {
                Log::error('Deepseek API JSON missing required keys: ' . json_encode($generatedData));
                return response()->json(['success' => false, 'message' => 'AI response missing required fields. Please try again.'], 500);
            }

            // Further validate settings if needed
            if (!in_array($generatedData['settings']['default_priority'], ['low', 'medium', 'high'])) {
                $generatedData['settings']['default_priority'] = 'medium'; // Default if invalid
            }
            if (isset($generatedData['settings']['execution_mode']) && !in_array($generatedData['settings']['execution_mode'], ['sequential', 'parallel'])) {
                unset($generatedData['settings']['execution_mode']); // Remove if invalid
            }

            return response()->json(['success' => true, 'data' => $generatedData]);
        } catch (\Exception $e) {
            Log::error('Error calling Deepseek API: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred during AI generation.'], 500);
        }
    }
}
