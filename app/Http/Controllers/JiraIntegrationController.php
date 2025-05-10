<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\OAuthState;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Epic;
use App\Models\Story;
use App\Models\Team;
use App\Services\JiraApiClient;
use App\Services\JiraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\JsonResponse;

class JiraIntegrationController extends Controller
{
    use JsonResponse;

    protected JiraApiClient $jiraClient;

    public function __construct(JiraApiClient $jiraClient)
    {
        $this->jiraClient = $jiraClient;
    }

    /**
     * Show the Jira integration dashboard
     */
    public function dashboard(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        $jiraConnected = false;
        $jiraProjects = [];

        try {
            if ($this->isJiraConnectedForTeam($currentTeamId)) {
                $jiraConnected = true;
                $jiraService = new JiraService($currentTeamId);
                $jiraProjects = $jiraService->getProjects();
            }
        } catch (\Exception $e) {
            Log::error('Error getting Jira projects: ' . $e->getMessage());
        }

        $existingProjects = $team->projects()->get(['id', 'name']);

        return view('dashboard.integrations.jira-dashboard', compact('jiraConnected', 'jiraProjects', 'existingProjects', 'team'));
    }

    /**
     * Get Jira project details including epics, stories, and issues
     */
    public function getProjectDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        try {
            $jiraService = new JiraService($currentTeamId);
            $projectKey = $request->input('key');

            // Get project details
            $projects = $jiraService->getProjects();
            $project = collect($projects)->firstWhere('key', $projectKey);

            if (!$project) {
                return $this->errorResponse("Project not found: {$projectKey}", 404);
            }

            // Get epics
            $epics = $jiraService->getIssuesWithJql(
                "project = \"{$projectKey}\" AND issuetype = Epic ORDER BY created DESC",
                ['summary', 'description', 'status']
            );

            // Get stories and tasks
            $stories = $jiraService->getIssuesWithJql(
                "project = \"{$projectKey}\" AND issuetype in (Story, Task) AND \"Epic Link\" is not EMPTY ORDER BY created DESC",
                ['summary', 'description', 'status', 'parent', 'labels']
            );

            // Get unassigned issues (not linked to epics)
            $unassigned = $jiraService->getIssuesWithJql(
                "project = \"{$projectKey}\" AND issuetype in (Story, Task, Bug) AND \"Epic Link\" is EMPTY ORDER BY created DESC",
                ['summary', 'description', 'status', 'issuetype', 'labels']
            );

            return $this->successResponse([
                'project' => $project,
                'epics' => $epics,
                'stories' => $stories,
                'unassigned' => $unassigned
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting Jira project details: ' . $e->getMessage());
            return $this->errorResponse('Failed to get project details: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Import issues from Jira to a project
     */
    public function importIssues(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_key' => 'required|string',
            'create_new_project' => 'required|boolean',
            'arxitest_project_id' => 'required_if:create_new_project,false|uuid|exists:projects,id',
            'new_project_name' => 'required_if:create_new_project,true|string|max:255',
            'issues' => 'required|array',
            'issues.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        try {
            $jiraService = new JiraService($currentTeamId);
            $projectKey = $request->input('project_key');
            $issueKeys = $request->input('issues');
            $createNewProject = $request->input('create_new_project');

            // Create or get project
            if ($createNewProject) {
                $project = Project::create([
                    'name' => $request->input('new_project_name'),
                    'description' => "Imported from Jira project: {$projectKey}",
                    'team_id' => $currentTeamId,
                    'settings' => [
                        'jira_import' => [
                            'source' => $projectKey,
                            'date' => now()->toDateTimeString()
                        ]
                    ],
                ]);
            } else {
                $project = Project::findOrFail($request->input('arxitest_project_id'));
                if ($project->team_id !== $currentTeamId) {
                    return $this->errorResponse('You do not have access to this project.', 403);
                }
            }

            // Collect all issues to import
            $jql = 'key in (' . implode(',', $issueKeys) . ')';
            $fields = ['summary', 'description', 'issuetype', 'parent', 'status', 'created', 'updated', 'labels', 'priority', 'components'];
            $issues = $jiraService->getIssuesWithJql($jql, $fields);

            // Process the issues
            $results = $this->processJiraImport($issues, $project);

            return $this->successResponse([
                'project_id' => $project->id,
                'imported' => [
                    'epics' => $results['epicCount'],
                    'stories' => $results['storyCount'],
                    'test_cases' => $results['testCaseCount']
                ],
                'redirect' => route('dashboard.projects.show', $project->id)
            ], 'Import completed successfully');
        } catch (\Exception $e) {
            Log::error('Error importing Jira issues: ' . $e->getMessage());
            return $this->errorResponse('Failed to import issues: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Process import of Jira issues
     */
    private function processJiraImport(array $issues, Project $project): array
    {
        $epicCount = 0;
        $storyCount = 0;
        $testCaseCount = 0;
        $epicMap = [];
        $batchId = uniqid('import_');

        // First pass: Create epics
        foreach ($issues as $issue) {
            if ($issue['fields']['issuetype']['name'] === 'Epic') {
                $epicData = [
                    'project_id' => $project->id,
                    'name' => $issue['fields']['summary'],
                    'external_id' => $issue['key'],
                    'status' => $issue['fields']['status']['name'],
                ];

                $epic = Epic::updateOrCreate(
                    ['external_id' => $issue['key'], 'project_id' => $project->id],
                    $epicData
                );

                $epicMap[$issue['id']] = $epic->id;
                $epicCount++;
            }
        }

        // Second pass: Create stories and test cases
        foreach ($issues as $issue) {
            $issueType = $issue['fields']['issuetype']['name'];

            if (in_array($issueType, ['Story', 'Task', 'Bug'])) {
                // Create or update story
                $storyData = [
                    'project_id' => $project->id,
                    'title' => $issue['fields']['summary'],
                    'description' => $this->extractPlainTextFromAdf($issue['fields']['description'] ?? []),
                    'metadata' => [
                        'jira_id' => $issue['id'],
                        'jira_key' => $issue['key'],
                        'jira_issue_type' => $issueType,
                        'jira_status' => $issue['fields']['status']['name'],
                        'jira_labels' => $issue['fields']['labels'] ?? [],
                        'import_batch_id' => $batchId,
                        'import_timestamp' => now()->toIso8601String()
                    ],
                    'source' => 'jira',
                    'external_id' => $issue['key']
                ];

                // Associate with epic if available
                $epicLink = $issue['fields']['parent']['id'] ?? null;
                if ($epicLink && isset($epicMap[$epicLink])) {
                    $storyData['epic_id'] = $epicMap[$epicLink];
                }

                $story = Story::updateOrCreate(
                    ['external_id' => $issue['key'], 'source' => 'jira'],
                    $storyData
                );

                $storyCount++;

                // Create test case
                $testCaseData = [
                    'title' => "Verify: " . substr($issue['fields']['summary'], 0, 90),
                    'description' => $this->extractPlainTextFromAdf($issue['fields']['description'] ?? []),
                    'story_id' => $story->id,
                    'steps' => $this->generateTestSteps([], $issue['fields']['summary'], $issueType),
                    'expected_results' => $this->generateExpectedResults([], $issue['fields']['summary'], $issueType),
                    'priority' => $this->mapJiraPriorityToArxitest(
        $issue['fields']['priority']['name'] ?? 'Medium'
    ),
    'status' => 'draft',
    'tags' => array_merge(
        ['jira-import', $issue['key'], strtolower($issueType)],
        $issue['fields']['labels'] ?? []
    )
];

$testCase = \App\Models\TestCase::firstOrCreate(
    [
        'story_id' => $story->id,
        'title' => "Verify: " . substr($issue['fields']['summary'], 0, 90)
    ],
    $testCaseData
);

$testCaseCount++;
            }
        }

        return [
            'epicCount' => $epicCount,
            'storyCount' => $storyCount,
            'testCaseCount' => $testCaseCount,
            'batchId' => $batchId
        ];
    }

    /**
     * Map Jira priority to Arxitest priority.
     */
    private function mapJiraPriorityToArxitest(string $jiraPriority): string
    {
        // Map common Jira priorities to Arxitest priorities
        $mapping = [
            'Highest' => 'high',
            'High' => 'high',
            'Medium' => 'medium',
            'Low' => 'low',
            'Lowest' => 'low',
            'Critical' => 'high',
            'Major' => 'high',
            'Minor' => 'medium',
            'Trivial' => 'low',
            'Blocker' => 'high'
        ];

        return $mapping[trim($jiraPriority)] ?? 'medium';
    }

    /**
     * Extract plain text from Jira's Atlassian Document Format.
     */
    private function extractPlainTextFromAdf(array $doc): string
    {
        $text = '';
        if (empty($doc)) return $text;

        array_walk_recursive($doc, function ($value, $key) use (&$text) {
            // Whenever we see a "text" key, grab its value
            if ($key === 'text') {
                $text .= $value;
            }
        });
        return $text;
    }

    /**
     * Generate test steps from available information and issue type.
     */
    private function generateTestSteps(array $criteria, string $storyTitle, string $issueType = 'Story'): array
    {
        // If we have acceptance criteria, generate more specific steps
        if (!empty($criteria)) {
            $steps = ['Navigate to relevant feature'];

            foreach ($criteria as $criterion) {
                // Convert AC to test step format
                // Remove prefixes like "Given", "When", "Then"
                $step = preg_replace('/^(given|when|then)\s+/i', '', $criterion);

                // Convert to action-oriented language
                $step = preg_replace('/user should be able to/i', 'Verify user can', $step);
                $step = preg_replace('/system should/i', 'Verify system', $step);

                $steps[] = ucfirst($step);
            }

            return $steps;
        }

        // If no criteria, create type-specific default steps
        switch (strtolower($issueType)) {
            case 'bug':
                return [
                    'Navigate to affected feature',
                    'Reproduce the issue described in: ' . $storyTitle,
                    'Verify issue has been fixed'
                ];
            case 'task':
                return [
                    'Navigate to relevant feature',
                    'Verify completion of task: ' . $storyTitle
                ];
            case 'story':
            default:
                return [
                    'Navigate to relevant feature',
                    'Perform actions described in: ' . $storyTitle,
                    'Verify functionality works as expected'
                ];
        }
    }

    /**
     * Generate expected results based on available information and issue type.
     */
    private function generateExpectedResults(array $criteria, string $storyTitle, string $issueType = 'Story'): string
    {
        if (!empty($criteria)) {
            // Convert criteria to verification statements
            $results = array_map(function ($criterion) {
                // Remove prefixes and convert to verification language
                $result = preg_replace('/^(given|when|then)\s+/i', '', $criterion);
                $result = preg_replace('/user should be able to/i', 'User can', $result);
                $result = preg_replace('/system should/i', 'System', $result);

                return "- " . ucfirst($result);
            }, $criteria);

            return "All acceptance criteria are met:\n" . implode("\n", $results);
        }

        // Type-specific fallback results
        switch (strtolower($issueType)) {
            case 'bug':
                return "The issue described in \"$storyTitle\" is resolved and can no longer be reproduced.";
            case 'task':
                return "The task \"$storyTitle\" is completed successfully.";
            case 'story':
            default:
                return "Feature works as described in story \"$storyTitle\".";
        }
    }

    /**
     * Check if Jira is connected for a team
     */
    private function isJiraConnectedForTeam(string $teamId): bool
    {
        return ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $teamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Redirect to Jira authorization page
     */
    public function redirect(Request $request)
    {
        $userId = Auth::id();
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        $state = OAuthState::generateState($userId, $currentTeamId);

        $query = http_build_query([
            'audience'     => 'api.atlassian.com',
            'client_id'    => $this->jiraClient->getClientId(),
            'scope'        => 'read:jira-user read:jira-work write:jira-work offline_access',
            'redirect_uri' => $this->jiraClient->getRedirectUri(),
            'state'        => $state,
            'response_type' => 'code',
            'prompt'       => 'consent',
        ]);

        return redirect($this->jiraClient->getBaseUri() . '/authorize?' . $query);
    }

    /**
     * Handle the callback from Jira authorization
     */
    public function callback(Request $request)
    {
        $stateParam = $request->state;
        if (empty($stateParam)) {
            return redirect()->route('dashboard.integrations.index')->with('error', 'Invalid OAuth callback: missing state.');
        }

        $oauthState = OAuthState::where('state_token', $stateParam)
            ->where('expires_at', '>', now())
            ->first();

        if (!$oauthState) {
            return redirect()->route('dashboard.integrations.index')->with('error', 'OAuth state verification failed.');
        }

        $userId = $oauthState->user_id;
        $teamId = $oauthState->project_id;
        $oauthState->delete();

        try {
            // Exchange the code for an access token
            $tokenData = $this->jiraClient->exchangeCode($request->code);
            $resources = $this->jiraClient->getResources($tokenData['access_token']);
            $site = $resources[0];

            // Create or update the integration record
            $integration = Integration::firstOrCreate(
                ['type' => Integration::TYPE_JIRA],
                [
                    'name' => 'Jira',
                    'base_url' => $site['url'],
                    'is_active' => true
                ]
            );

            // Find a project to store the integration with
            $team = Team::find($teamId);
            $project = $team->projects()->first() ?? Project::create([
                'name' => $team->name . ' – Jira Credentials',
                'description' => 'Holds Jira OAuth tokens for the team.',
                'team_id' => $team->id,
                'settings' => ['is_placeholder' => true],
            ]);

            // Store encrypted credentials
            $credentials = [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => now()->addSeconds($tokenData['expires_in'])->timestamp,
                'cloud_id' => $site['id'],
                'site_url' => $site['url'],
                'created_at' => now()->timestamp,
            ];

            $encrypted = Crypt::encryptString(json_encode($credentials));

            // Update or create project integration
            ProjectIntegration::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'integration_id' => $integration->id
                ],
                [
                    'encrypted_credentials' => $encrypted,
                    'is_active' => true,
                    'project_specific_config' => [
                        'cloud_id' => $site['id'],
                        'site_url' => $site['url'],
                    ],
                ]
            );

            // Important - This block ensures the user is logged in when returning from OAuth
            if (!Auth::check()) {
                Auth::loginUsingId($userId, true);
                session(['current_team' => $teamId]);
                session()->save();
            }

            return redirect()->route('dashboard.integrations.jira.dashboard')
                ->with('success', 'Jira connected successfully! You can now browse and import issues.');
        } catch (\Exception $e) {
            Log::error('Jira OAuth error', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Jira authorization failed: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Jira integration
     */
    public function disconnect(Request $request)
    {
        $currentTeamId = session('current_team');
        if (! $currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Team context is missing.');
        }

        $jiraIntegration = Integration::where('type', Integration::TYPE_JIRA)->first();
        if (! $jiraIntegration) {
            return redirect()->route('dashboard.integrations.index')->with('info', 'No Jira integration config found.');
        }

        $deleted = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
            ->where('integration_id', $jiraIntegration->id)
            ->delete();

        if ($deleted) {
            return redirect()->route('dashboard.integrations.index')->with('success', 'Jira disconnected successfully.');
        }

        return redirect()->route('dashboard.integrations.index')->with('info', 'No active Jira connections to disconnect.');
    }
}
