<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\OAuthState;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\Team;
use App\Models\User;
use App\Services\JiraApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class IntegrationController extends Controller
{
    protected JiraApiClient $jiraClient;

    public function __construct(JiraApiClient $jiraClient)
    {
        $this->jiraClient = $jiraClient;
    }

    public function index(Request $request)
    {
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        $jiraConnected = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_JIRA))
            ->where('is_active', true)
            ->exists();

        $githubConnected = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $currentTeamId))
            ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_GITHUB))
            ->where('is_active', true)
            ->exists();

        return view('dashboard.integrations.index', compact('jiraConnected', 'githubConnected', 'team'));
    }

    public function jiraRedirect(Request $request)
    {
        $userId = Auth::id();
        $team = $this->getCurrentTeam($request);
        $currentTeamId = $team->id;

        $state = OAuthState::generateState($userId, $currentTeamId);

        $query = http_build_query([
            'audience'     => 'api.atlassian.com',
            'client_id'    => $this->jiraClient->config('client_id'),
            'scope'        => 'read:jira-user read:jira-work write:jira-work offline_access',
            'redirect_uri' => $this->jiraClient->config('redirect'),
            'state'        => $state,
            'response_type'=> 'code',
            'prompt'       => 'consent',
        ]);

        return redirect($this->jiraClient->config('base_uri') . '/authorize?' . $query);
    }

    public function jiraCallback(Request $request)
    {
        $stateParam = $request->state;
        if (empty($stateParam)) {
            return redirect()->route('login')->with('error', 'Invalid OAuth callback: missing state.');
        }

        $oauthState = OAuthState::where('state_token', $stateParam)
            ->where('expires_at', '>', now())
            ->first();

        if (! $oauthState) {
            return redirect()->route('login')->with('error', 'OAuth state verification failed.');
        }

        $userId = $oauthState->user_id;
        $teamId = $oauthState->project_id;
        $oauthState->delete();

        try {
            $tokenData = $this->jiraClient->exchangeCode($request->code);
            $resources = $this->jiraClient->getResources($tokenData['access_token']);
            $site      = $resources[0];
        } catch (\Exception $e) {
            Log::error('Jira OAuth error', ['error'=>$e->getMessage()]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Jira authorization failed: ' . $e->getMessage());
        }

        $credentials = [
            'access_token'  => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_at'    => now()->addSeconds($tokenData['expires_in'] ?? 3600)->timestamp,
            'cloud_id'      => $site['id'],
            'site_url'      => $site['url'],
            'site_name'     => $site['name'],
            'scopes'        => explode(' ', $tokenData['scope'] ?? ''),
        ];

        try {
            $encrypted = Crypt::encryptString(json_encode($credentials));

            $integration = Integration::firstOrCreate(
                ['type' => Integration::TYPE_JIRA],
                ['name' => 'Jira', 'base_url' => $this->jiraClient->config('api_uri'), 'is_active' => true]
            );

            $team = Team::find($teamId);

            $project = $team->projects()->first() ?? Project::create([
                'name'        => $team->name . ' – Jira Credentials',
                'description' => 'Holds Jira OAuth tokens for the team.',
                'team_id'     => $team->id,
                'settings'    => ['is_placeholder' => true],
            ]);

            ProjectIntegration::updateOrCreate(
                ['project_id'     => $project->id, 'integration_id' => $integration->id],
                [
                    'encrypted_credentials' => $encrypted,
                    'is_active'             => true,
                    'project_specific_config'=> [
                        'cloud_id'  => $site['id'],
                        'site_url'  => $site['url'],
                        'site_name' => $site['name'],
                    ],
                ]
            );
        } catch (\Exception $e) {
            Log::error('Storing Jira credentials failed', ['error'=>$e->getMessage()]);
            return redirect()->route('dashboard.integrations.index')
                ->with('error', 'Failed to save Jira connection: ' . $e->getMessage());
        }

        if (! Auth::check()) {
            Auth::loginUsingId($userId, true);
            session(['current_team' => $teamId]);
        }

        return redirect()->route('dashboard.integrations.index')
            ->with('success', 'Jira connected successfully.');
    }

    public function jiraDisconnect(Request $request)
    {
        $currentTeamId = session('current_team');
        if (! $currentTeamId) {
            return redirect()->route('dashboard.select-team')->with('error', 'Team context is missing.');
        }

        $jiraIntegration = Integration::where('type', Integration::TYPE_JIRA)->first();
        if (! $jiraIntegration) {
            return redirect()->route('dashboard.integrations.index')->with('info', 'No Jira integration config found.');
        }

        $deleted = ProjectIntegration::whereHas('project', fn($q)=>$q->where('team_id', $currentTeamId))
            ->where('integration_id', $jiraIntegration->id)
            ->delete();

        if ($deleted) {
            return redirect()->route('dashboard.integrations.index')->with('success', 'Jira disconnected successfully.');
        }

        return redirect()->route('dashboard.integrations.index')->with('info', 'No active Jira connections to disconnect.');
    }
}
