<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserBelongsToTeam
{
    public function handle(Request $request, Closure $next, $teamId = null)
    {
        $user = $request->user();

        // If specific team ID is provided
        if ($teamId) {
            if (!$user->teams()->where('teams.id', $teamId)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }
        // If team ID is in request
        elseif ($request->team_id) {
            if (!$user->teams()->where('teams.id', $request->team_id)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return $next($request);
    }
}
