<?php

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
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && session()->has('current_team')) {
            $teamId = session('current_team');

            // Check if GitHub is connected for this team
            $isConnected = ProjectIntegration::whereHas('project', fn($q) => $q->where('team_id', $teamId))
                ->whereHas('integration', fn($q) => $q->where('type', Integration::TYPE_GITHUB))
                ->where('is_active', true)
                ->exists();

            // Set a session variable for use in the layout
            session(['github_connected' => $isConnected]);
        }

        return $next($request);
    }
}
