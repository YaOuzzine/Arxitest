<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the team.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return bool
     */
    public function view(User $user, Team $team)
    {
        // User can view the team if they are a member
        return $team->users->contains($user->id);
    }

    /**
     * Determine whether the user can update the team.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return bool
     */
    public function update(User $user, Team $team)
    {
        // Find the pivot record to determine user's role in the team
        $pivot = $team->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$pivot) return false;

        // Only owners and admins can update the team
        return in_array($pivot->pivot->team_role, ['owner', 'admin']);
    }

    /**
     * Determine whether the user can delete the team.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return bool
     */
    public function delete(User $user, Team $team)
    {
        // Find the pivot record to determine user's role in the team
        $pivot = $team->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$pivot) return false;

        // Only owners can delete the team
        return $pivot->pivot->team_role === 'owner';
    }

    /**
     * Determine whether the user can manage members of the team.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return bool
     */
    public function manageMembers(User $user, Team $team)
    {
        // Find the pivot record to determine user's role in the team
        $pivot = $team->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$pivot) return false;

        // Only owners and admins can manage members
        return in_array($pivot->pivot->team_role, ['owner', 'admin']);
    }
}
