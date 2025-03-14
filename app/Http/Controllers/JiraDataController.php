<?php

namespace App\Http\Controllers;

use App\Models\JiraStory;
use App\Models\Project;
use App\Models\TestSuite;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JiraDataController extends Controller
{
    /**
     * Show list of Jira projects available for import
     */
    public function index()
    {
        // Check if connected to Jira
        $token = session('jira_access_token');
        $cloudId = session('jira_cloud_id');

        if (!$token || !$cloudId) {
            return redirect('/jira/oauth')->with('error', 'Not authenticated with Jira');
        }

        try {
            // Fetch available Jira projects
            $projUrl = "https://api.atlassian.com/ex/jira/{$cloudId}/rest/api/3/project";
            $projResponse = Http::withoutVerifying()->withToken($token)->get($projUrl);

            if ($projResponse->failed()) {
                $errorBody = $projResponse->body();
                Log::error("Failed to fetch Jira projects: " . $errorBody);
                return redirect()->back()->with('error', 'Failed to fetch projects from Jira: ' . $errorBody);
            }

            $jiraProjects = $projResponse->json();

            // Get user's teams for project assignment
            $teams = Auth::user()->teams()->get();

            // Get existing project keys to show which are already imported
            $existingProjects = Project::where(function($query) {
                $query->whereNotNull('settings->jira_project_key')
                      ->orWhereNotNull('settings->jira_id');
            })->get();

            $existingJiraKeys = $existingProjects->pluck('settings.jira_project_key')->filter()->toArray();
            $existingJiraIds = $existingProjects->pluck('settings.jira_id')->filter()->toArray();

            $jiraSiteName = session('jira_site_name');

            return view('jira.import', compact('jiraProjects', 'teams', 'existingJiraKeys', 'existingJiraIds', 'jiraSiteName'));
        } catch (\Exception $e) {
            Log::error("Error preparing Jira import: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error preparing Jira import: ' . $e->getMessage());
        }
    }

    /**
     * Process Jira project import
     */
    public function importData(Request $request)
    {
        $token = session('jira_access_token');
        $cloudId = session('jira_cloud_id');

        if (!$token || !$cloudId) {
            return redirect('/jira/oauth')->with('error', 'Not authenticated with Jira');
        }

        // Validate input
        $validated = $request->validate([
            'jira_projects' => 'required|array',
            'jira_projects.*' => 'required|string',
            'team_id' => 'required|exists:teams,id',
            'update_existing' => 'boolean',
            'include_metadata' => 'boolean'
        ]);

        $teamId = $validated['team_id'];
        $jiraProjectKeys = $validated['jira_projects'];
        $updateExisting = $request->boolean('update_existing', true);
        $includeMetadata = $request->boolean('include_metadata', true);

        try {
            $importedProjects = 0;
            $totalImportedStories = 0;
            $createdProjects = [];

            // Loop through selected Jira projects
            foreach ($jiraProjectKeys as $jiraKey) {
                // Get detailed project info from Jira
                $projectUrl = "https://api.atlassian.com/ex/jira/{$cloudId}/rest/api/3/project/{$jiraKey}";
                $projectResponse = Http::withoutVerifying()->withToken($token)->get($projectUrl);

                if ($projectResponse->failed()) {
                    Log::warning("Failed to fetch details for Jira project {$jiraKey}: " . $projectResponse->body());
                    continue;
                }

                $jiraProject = $projectResponse->json();

                // Check if project already exists
                $existingProject = Project::where(function($query) use ($jiraKey, $jiraProject) {
                    $query->where('settings->jira_project_key', $jiraKey)
                          ->orWhere('settings->jira_id', $jiraProject['id']);
                })->first();

                if ($existingProject && !$updateExisting) {
                    // Skip this project if it exists and we're not updating
                    continue;
                }

                // Create or update project
                $project = $existingProject ?: new Project();

                if (!$existingProject) {
                    // Only set these fields for new projects
                    $project->team_id = $teamId;
                    $project->name = $jiraProject['name'];
                    $project->description = $jiraProject['description'] ?? 'Imported from Jira';
                }

                // Always update settings
                $settings = $project->settings ?? [];
                $settings['jira_project_key'] = $jiraKey;
                $settings['jira_id'] = $jiraProject['id'];
                $settings['jira_type'] = $jiraProject['projectTypeKey'] ?? null;
                $settings['jira_cloud_id'] = $cloudId;
                $settings['jira_imported_at'] = now()->toDateTimeString();

                $project->settings = $settings;
                $project->save();

                // Create default test suite if it doesn't exist
                $testSuite = TestSuite::firstOrCreate(
                    ['project_id' => $project->id, 'name' => 'Default'],
                    ['description' => 'Default test suite created from Jira import']
                );

                // Import stories for this project
                $importedStories = $this->importStoriesForProject($token, $cloudId, $jiraKey, $testSuite->id, $includeMetadata);
                $totalImportedStories += $importedStories;

                $importedProjects++;
                $createdProjects[] = $project;
            }

            if ($importedProjects === 0) {
                return redirect()->route('projects.index')
                    ->with('info', 'No new projects were imported from Jira.');
            }

            if (count($createdProjects) === 1) {
                // If only one project was created/updated, redirect to that project
                return redirect()->route('projects.show', $createdProjects[0])
                    ->with('success', "Successfully imported {$totalImportedStories} Jira stories into project '{$createdProjects[0]->name}'");
            }

            // If multiple projects were created, redirect to projects index
            return redirect()->route('projects.index')
                ->with('success', "Successfully imported {$importedProjects} projects with {$totalImportedStories} Jira stories");

        } catch (\Exception $e) {
            Log::error("Error importing Jira data: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error importing Jira data: ' . $e->getMessage());
        }
    }

    /**
     * Import stories for a specific project
     */
    private function importStoriesForProject($token, $cloudId, $jiraProjectKey, $testSuiteId, $includeMetadata)
    {
        // Get available issue types first
        $typesUrl = "https://api.atlassian.com/ex/jira/{$cloudId}/rest/api/3/issuetype";
        $typesResponse = Http::withoutVerifying()->withToken($token)->get($typesUrl);

        $issueTypes = [];
        if (!$typesResponse->failed()) {
            $types = $typesResponse->json();
            foreach ($types as $type) {
                $issueTypes[] = $type['name'];
            }
        }

        // Build JQL to get issues for this project
        $jql = 'project = "' . $jiraProjectKey . '" ORDER BY updated DESC';

        // Add issue type filtering if we have types
        if (!empty($issueTypes)) {
            // Prioritize story and bug types
            $storyLikeTypes = array_filter($issueTypes, function($type) {
                return stripos($type, 'story') !== false ||
                       $type === 'User Story' ||
                       $type === 'Story';
            });

            $bugLikeTypes = array_filter($issueTypes, function($type) {
                return stripos($type, 'bug') !== false ||
                       stripos($type, 'defect') !== false ||
                       $type === 'Bug';
            });

            $taskLikeTypes = array_filter($issueTypes, function($type) {
                return stripos($type, 'task') !== false ||
                       $type === 'Task';
            });

            $preferredTypes = array_merge($storyLikeTypes, $bugLikeTypes, $taskLikeTypes);

            if (!empty($preferredTypes)) {
                $typesStr = implode('", "', array_values($preferredTypes));
                $jql = 'project = "' . $jiraProjectKey . '" AND issuetype in ("' . $typesStr . '") ORDER BY updated DESC';
            }
        }

        $importCount = 0;
        $startAt = 0;
        $maxResults = 50;
        $hasMore = true;

        while ($hasMore) {
            // Fetch issues
            $issueUrl = "https://api.atlassian.com/ex/jira/{$cloudId}/rest/api/3/search";
            $issueResponse = Http::withoutVerifying()->withToken($token)->get($issueUrl, [
                'jql' => $jql,
                'fields' => 'summary,description,issuetype,status,assignee,project,labels,created,updated',
                'startAt' => $startAt,
                'maxResults' => $maxResults
            ]);

            if ($issueResponse->failed()) {
                Log::error("Failed to fetch issues for project {$jiraProjectKey}: " . $issueResponse->body());
                break;
            }

            $data = $issueResponse->json();
            $stories = $data['issues'] ?? [];
            $total = $data['total'] ?? 0;

            foreach ($stories as $story) {
                $fields = $story['fields'];

                // Process description (handle ADF format)
                $description = '';
                if (isset($fields['description'])) {
                    if (is_array($fields['description'])) {
                        $description = $this->convertAdfToPlainText($fields['description']);
                    } else {
                        $description = $fields['description'];
                    }
                }

                // Build metadata
                $metadata = [];

                if ($includeMetadata) {
                    $metadata = [
                        'issue_type' => $fields['issuetype']['name'] ?? null,
                        'status' => $fields['status']['name'] ?? null,
                        'assignee' => $fields['assignee']['displayName'] ?? null,
                        'project_key' => $fields['project']['key'] ?? null,
                        'project_name' => $fields['project']['name'] ?? null,
                        'labels' => $fields['labels'] ?? [],
                        'created' => $fields['created'] ?? null,
                        'updated' => $fields['updated'] ?? null,
                        'test_suite_id' => $testSuiteId
                    ];
                }

                // Create or update story
                JiraStory::updateOrCreate(
                    ['jira_key' => $story['key']],
                    [
                        'title' => $fields['summary'] ?? ('Story ' . $story['key']),
                        'description' => $description,
                        'metadata' => $metadata
                    ]
                );

                $importCount++;
            }

            // Check if there are more pages
            $startAt += count($stories);
            $hasMore = $startAt < $total;
        }

        return $importCount;
    }

    /**
     * Convert Atlassian Document Format to plain text
     */
    private function convertAdfToPlainText($adf)
    {
        if (!is_array($adf)) {
            return $adf;
        }

        $plainText = '';

        // Handle document structure
        if (isset($adf['type']) && $adf['type'] === 'doc') {
            foreach ($adf['content'] ?? [] as $content) {
                $plainText .= $this->extractTextFromAdfNode($content) . "\n";
            }
        }

        // Handle direct content array
        if (isset($adf[0]) && is_array($adf[0])) {
            foreach ($adf as $content) {
                $plainText .= $this->extractTextFromAdfNode($content) . "\n";
            }
        }

        return trim($plainText);
    }

    /**
     * Extract text from an ADF node
     */
    private function extractTextFromAdfNode($node)
    {
        $text = '';

        // Extract text based on node type
        if (isset($node['type'])) {
            switch ($node['type']) {
                case 'paragraph':
                case 'heading':
                case 'listItem':
                case 'bulletList':
                case 'orderedList':
                    foreach ($node['content'] ?? [] as $content) {
                        $text .= $this->extractTextFromAdfNode($content);
                    }
                    $text .= "\n";
                    break;

                case 'text':
                    $text .= $node['text'] ?? '';
                    break;

                case 'hardBreak':
                    $text .= "\n";
                    break;

                default:
                    // For other node types, recursively process content
                    if (isset($node['content']) && is_array($node['content'])) {
                        foreach ($node['content'] as $content) {
                            $text .= $this->extractTextFromAdfNode($content);
                        }
                    }
            }
        }

        return $text;
    }
}
