<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvitationController extends Controller
{
    /**
     * Handle invitation acceptance for non-logged in users
     */
    public function accept($token)
    {
        $invitation = TeamInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired or is invalid.');
        }

        // If user is not logged in, redirect to login with invitation token
        if (!Auth::check()) {
            // Store invitation token in session
            session(['invitation_token' => $token]);

            // Check if there's a user with this email already
            $user = User::where('email', $invitation->email)->first();

            if ($user) {
                // User exists but not logged in, redirect to login
                return redirect()->route('login')
                    ->with('info', "Please log in to your account to accept this invitation to join {$invitation->team->name}.");
            } else {
                // No user with this email, redirect to registration
                return redirect()->route('register')
                    ->with('info', "Please create an account to accept this invitation to join {$invitation->team->name}.");
            }
        }

        // User is logged in, check if the invitation is for them
        if (Auth::user()->email !== $invitation->email) {
            return redirect()->route('dashboard')
                ->with('error', 'This invitation was sent to a different email address.');
        }

        // Process the invitation for the logged-in user
        return $this->processInvitation($invitation);
    }

    /**
     * Complete invitation acceptance after login/registration
     */
    public function complete()
    {
        $token = session('invitation_token');

        if (!$token) {
            return redirect()->route('dashboard')
                ->with('error', 'No pending invitation found.');
        }

        $invitation = TeamInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            session()->forget('invitation_token');
            return redirect()->route('dashboard')
                ->with('error', 'The invitation has expired or is invalid.');
        }

        // Check if the invitation email matches the current user's email
        if (Auth::user()->email !== $invitation->email) {
            session()->forget('invitation_token');
            return redirect()->route('dashboard')
                ->with('error', 'This invitation was sent to a different email address.');
        }

        return $this->processInvitation($invitation);
    }

    /**
     * Accept invitation directly (used in the select-team view)
     */
    public function acceptDirectly($token)
    {
        if (!Auth::check()) {
            session(['invitation_token' => $token]);
            return redirect()->route('login')
                ->with('info', 'Please log in to accept this invitation.');
        }

        $invitation = TeamInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'This invitation has expired or is invalid.');
        }

        if (Auth::user()->email !== $invitation->email) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'This invitation was sent to a different email address.');
        }

        return $this->processInvitation($invitation);
    }

    /**
     * Reject an invitation
     */
    public function reject($id)
    {
        $invitation = TeamInvitation::find($id);

        if (!$invitation || Auth::user()->email !== $invitation->email) {
            return redirect()->route('dashboard.select-team')
                ->with('error', 'Invalid invitation.');
        }

        // Delete the invitation
        $invitation->delete();

        return redirect()->route('dashboard.select-team')
            ->with('success', 'Invitation rejected successfully.');
    }

    /**
     * Common method to process valid invitations
     */
    private function processInvitation(TeamInvitation $invitation)
    {
        $user = Auth::user();
        $team = $invitation->team;

        // Check if user is already a member
        if ($team->users->contains($user->id)) {
            // Already a member, just remove the invitation
            $invitation->delete();
            session()->forget('invitation_token');

            return redirect()->route('dashboard.select-team')
                ->with('info', "You are already a member of {$team->name}.");
        }

        // Add user to team
        $team->users()->attach($user->id, [
            'team_role' => $invitation->role
        ]);

        // Delete the invitation after processing
        $invitation->delete();

        // Clear the session
        session()->forget('invitation_token');

        // Set this team as current
        session(['current_team' => $team->id]);

        return redirect()->route('dashboard')
            ->with('success', "Welcome to {$team->name}! You've successfully joined as {$invitation->role}.");
    }
}
