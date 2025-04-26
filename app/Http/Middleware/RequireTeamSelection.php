<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequireTeamSelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if team is selected
        $currentTeamId = session('current_team');
        if (!$currentTeamId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team selection is required',
                    'redirect' => route('dashboard.select-team')
                ], 403);
            }
            return redirect()->route('dashboard.select-team')
                ->with('error', 'Please select a team first.');
        }

        // Ensure the team exists and user is a member
        $user = Auth::user();
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            return redirect()->route('login');
        }

        $team = $user->teams()->find($currentTeamId);
        if (!$team) {
            // If team doesn't exist or user isn't a member, clear the session and redirect
            Log::warning('Team validation failed: Session team invalid or user not member.', [
                'user_id' => $user->id,
                'session_current_team' => $currentTeamId
            ]);

            session()->forget('current_team');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid team selection',
                    'redirect' => route('dashboard.select-team')
                ], 403);
            }

            return redirect()->route('dashboard.select-team')
                ->with('error', 'Invalid team selection. Please re-select.');
        }

        // Share the team with the view
        $request->attributes->add(['current_team' => $team]);

        return $next($request);
    }
}
