<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class JiraAuthController extends Controller
{
    public function redirectToJira()
    {
        // Generating a randome state string for security
        $state = Str::random(40);
        session(['jira_oauth_state' => $state]);

        $queryParams = http_build_query([
            'audience' => 'api.atlassian.com',
            'client_id' => env('ATLASSIAN_CLIENT_ID'),
            'scope'         => 'read:jira-work read:jira-user',
            'redirect_uri'  => env('ATLASSIAN_OAUTH_CALLBACK'),
            'state'         => $state,
            'response_type' => 'code',
            'prompt'        => 'consent'
        ]);

        $authUrl = "https://auth.atlassian.com/authorize?{$queryParams}";
        return redirect($authUrl);
    }

    public function handleCallback(Request $request)
    {
        // Verify state to prevent CSRF

        $state = $request->input('state');

        if (!$state || $state !== session('jira_oauth_state')) {
            return redirect('/')->with('error', 'Invalid OAuth state');
        }

        // Clear the state from session

        session()->forget('jira_oauth_state');

        // Get the authorization code from query params
        $code = $request->input('code');
        if(!$code){
            return redirect('/')->with('error', 'No auth code recieved');
        }

        // Exchange the authorization code for acess (and refrehs) token
        // Without Verification (Will be changed in the second iteration)
        $tokenResponse = Http::withoutVerifying()->acceptJson()->post('https://auth.atlassian.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('ATLASSIAN_CLIENT_ID'),
            'client_secret' => env('ATLASSIAN_CLIENT_SECRET'),
            'code'          => $code,
            'redirect_uri'  => env('ATLASSIAN_OAUTH_CALLBACK'),
        ]);

        if ($tokenResponse->failed()) {
            return redirect('/')->with('error', 'Failed to obtain access token');
        }

        $tokens = $tokenResponse->json();
        // tokens now contains 'access_token', 'expires_in', 'scope', and possibly 'refresh_token'
        // This will be changed as soon as possible. Possibly within the second iteration.
        session(['jira_access_token' => $tokens['access_token']]);
        if (!empty($tokens['refresh_token'])) {
            session(['jira_refresh_token' => $tokens['refresh_token']]);
        }

        // Determine the Cloud ID (site identifier) for the Jira instance
        $accessToken = $tokens['access_token'];
        $resourceResponse = Http::withoutVerifying()->withToken($accessToken)
                ->get('https://api.atlassian.com/oauth/token/accessible-resources');
        $resources = $resourceResponse->json();

        if (empty($resources)) {
            return redirect('/')->with('error', 'No accessible Jira resources found for this account');
        }
        // If the user has access to multiple Jira sites, pick one (here we take the first)
        $site = $resources[0];
        session(['jira_cloud_id' => $site['id']]);
        session(['jira_site_name' => $site['name']]); // e.g., save the site name for display

        // Now the user is authenticated with Jira – redirect to a dashboard or import page
        return redirect('/jira/import');

    }
}
