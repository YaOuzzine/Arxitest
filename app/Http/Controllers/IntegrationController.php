<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Integration;
use App\Models\ProjectIntegration;

class IntegrationController extends Controller
{
    /**
     * Display the integrations management page
     */
    public function index()
    {
        // Get user's current integrations
        $integrations = Integration::where('is_active', true)->get();

        // Set Jira connection timestamp for display if available
        $jiraConnectedAt = session('jira_connected_at');

        return view('integrations.index', compact('integrations', 'jiraConnectedAt'));
    }

    /**
     * Disconnect from Jira integration
     */
    public function disconnectJira(Request $request)
    {
        // Clear Jira session data
        Session::forget([
            'jira_access_token',
            'jira_refresh_token',
            'jira_cloud_id',
            'jira_site_name',
            'jira_connected_at',
            'jira_oauth_state'
        ]);

        Log::info('User ID ' . Auth::id() . ' disconnected from Jira');

        return redirect()->route('integrations.index')
            ->with('success', 'Successfully disconnected from Jira');
    }

    /**
     * Reconnect to Jira with new domain
     */
    public function reconnectJira(Request $request)
    {
        // Validate the request
        $request->validate([
            'jira_domain' => 'nullable|string|max:255'
        ]);

        // Store the domain if provided
        if ($request->filled('jira_domain')) {
            session(['jira_domain' => $request->jira_domain]);
        } else {
            Session::forget('jira_domain');
        }

        // Clear existing token to force re-authentication
        Session::forget([
            'jira_access_token',
            'jira_refresh_token',
            'jira_cloud_id',
            'jira_site_name'
        ]);

        // Redirect to the OAuth flow
        return redirect('/jira/oauth');
    }

    /**
     * Generate a new API key
     */
    public function generateApiKey(Request $request)
    {
        $request->validate([
            'key_name' => 'required|string|max:255',
            'key_expiry' => 'required|integer|min:0'
        ]);

        // In a real implementation, generate and store API key for the user
        // For now, return a placeholder response

        return response()->json([
            'success' => true,
            'message' => 'API key generated successfully',
            'data' => [
                'name' => $request->key_name,
                'key' => 'arxt_' . bin2hex(random_bytes(16)),
                'expires_at' => $request->key_expiry > 0
                    ? now()->addDays($request->key_expiry)->toDateTimeString()
                    : null
            ]
        ]);
    }

    /**
     * Create a new webhook
     */
    public function createWebhook(Request $request)
    {
        $request->validate([
            'webhook_url' => 'required|url|max:255',
            'events' => 'required|array',
            'events.*' => 'string|in:test.execution,test.creation,jira.import',
            'webhook_secret' => 'nullable|string|max:255'
        ]);

        // In a real implementation, create and store webhook configuration
        // For now, return a placeholder response

        return response()->json([
            'success' => true,
            'message' => 'Webhook created successfully',
            'data' => [
                'url' => $request->webhook_url,
                'events' => $request->events,
                'secret' => $request->webhook_secret ? '••••••••' : null,
            ]
        ]);
    }
}
