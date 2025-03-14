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
        // User can view team if they are a member
        return $team->users()->where('user_id', $user->id)->exists();
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
        // User can update team if they are a member
        // In a real app, you might want to check for admin role
        return $team->users()->where('user_id', $user->id)->exists();
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
        // User can delete team if they are a member
        // In a real app, you might want to limit this to team owners or admins
        return $team->users()->where('user_id', $user->id)->exists();
    }
}
