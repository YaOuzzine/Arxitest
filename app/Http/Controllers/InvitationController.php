<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    /**
     * Accept a team invitation
     */
    public function accept($token)
    {
        $invitation = TeamInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'The invitation link is invalid or has expired.');
        }

        $team = $invitation->team;

        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is already a member of the team
            if (!$team->users->contains($user->id)) {
                $team->users()->attach($user->id, [
                    'team_role' => $invitation->role
                ]);
            }

            // Delete the invitation
            $invitation->delete();

            // Set current team in session
            session(['current_team' => $team->id]);

            return redirect()->route('dashboard')
                ->with('success', "You have joined the team '{$team->name}'.");
        }

        // Check if user with this email exists
        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            // User exists but not logged in - redirect to login
            return redirect()->route('login')
                ->with('info', "Please log in to accept the invitation to join '{$team->name}'.")
                ->with('invitation_token', $token);
        }

        // User doesn't exist - redirect to registration
        return redirect()->route('register')
            ->with('info', "Please create an account to join '{$team->name}'.")
            ->with('invitation_email', $invitation->email)
            ->with('invitation_token', $token);
    }

    /**
     * Complete invitation after registration/login
     */
    public function complete()
    {
        $token = session('invitation_token');

        if (!$token) {
            return redirect()->route('dashboard');
        }

        $invitation = TeamInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('dashboard')
                ->with('error', 'The invitation has expired or is no longer valid.');
        }

        $team = $invitation->team;
        $user = Auth::user();

        // Add user to team
        if (!$team->users->contains($user->id)) {
            $team->users()->attach($user->id, [
                'team_role' => $invitation->role
            ]);
        }

        // Delete the invitation
        $invitation->delete();

        // Set current team in session
        session(['current_team' => $team->id]);

        return redirect()->route('dashboard')
            ->with('success', "You have joined the team '{$team->name}'.");
    }

    /**
     * Directly accept an invitation (when user is already logged in)
     *
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptDirectly($token)
    {
        $invitation = TeamInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'The invitation is invalid or has expired.');
        }

        $team = $invitation->team;
        $user = Auth::user();

        // Check if the invitation email matches the logged-in user's email
        if ($invitation->email !== $user->email) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'This invitation was sent to a different email address.');
        }

        // Check if user is already a member of the team
        if (!$team->users->contains($user->id)) {
            $team->users()->attach($user->id, [
                'team_role' => $invitation->role
            ]);
        }

        // Delete the invitation
        $invitation->delete();

        // Set current team in session
        session(['current_team' => $team->id]);

        return redirect()->route('dashboard')
            ->with('success', "You have joined the team '{$team->name}'.");
    }

    /**
     * Reject an invitation
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject($id)
    {
        $invitation = TeamInvitation::findOrFail($id);
        $user = Auth::user();

        // Check if the invitation email matches the logged-in user's email
        if ($invitation->email !== $user->email) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'This invitation was sent to a different email address.');
        }

        $teamName = $invitation->team->name;

        // Delete the invitation
        $invitation->delete();

        return redirect()->route('dashboard.select-team')
            ->with('info', "You have declined the invitation to join '{$teamName}'.");
    }
}
