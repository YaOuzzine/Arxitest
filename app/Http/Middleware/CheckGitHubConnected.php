<?php
// app/Http/Middleware/CheckGitHubConnected.php

namespace App\Http\Middleware;

use App\Models\Integration;
use App\Models\ProjectIntegration;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckGitHubConnected
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && session()->has('current_team')) {
            $teamId = session('current_team');

            // Add debug logging to see what's happening
            Log::debug('CheckGitHubConnected middleware running', [
                'team_id' => $teamId,
                'current_user' => Auth::id()
            ]);

            // Check if GitHub is connected for this team
            $isConnected = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $teamId))
                ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_GITHUB))
                ->where('is_active', true)
                ->exists();

            Log::debug('GitHub connection status check result', [
                'is_connected' => $isConnected
            ]);

            // Set a session variable for use in the layout
            session(['github_connected' => $isConnected]);
        }

        return $next($request);
    }
}
