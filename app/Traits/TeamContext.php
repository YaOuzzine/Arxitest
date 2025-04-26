<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

trait TeamContext
{
    /**
     * Get the current team from the request.
     *
     * @param Request $request
     * @return Team
     */
    protected function getCurrentTeam(Request $request): Team
    {
        return $request->attributes->get('current_team');
    }

    /**
     * Determine if a project belongs to the current team.
     *
     * @param string $projectId
     * @param Team|null $team
     * @return bool
     */
    protected function isProjectInTeam(string $projectId, ?Team $team = null): bool
    {
        if (!$team) {
            $team = request()->attributes->get('current_team');
        }

        if (!$team) {
            return false;
        }

        return $team->projects()->where('id', $projectId)->exists();
    }
}
