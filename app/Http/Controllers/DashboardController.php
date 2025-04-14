<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\TeamInvitation;

class DashboardController extends Controller
{
    public function showDashboard(){
        return view('dashboard.index');
    }

    public function showSelectTeam()
    {
        $user = auth('web')->user();
        $teams = $user->teams()->with('users')->get();

        // Get pending invitations for the current user
        $pendingInvitations = TeamInvitation::where('email', $user->email)
            ->where('expires_at', '>', now())
            ->with('team')
            ->get();

        return view('dashboard.select-team', [
            'teams' => $teams,
            'user' => $user,
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function setCurrentTeam(Request $request){
        $team_id = $request->input('team_id');

        try{
            $team = auth('web')->user()->teams()->findOrFail($team_id);
        }
        catch (ModelNotFoundException $e){
            return back()->withErrors([
                'team_id' => 'You do not belong to that team.'
            ]);
        }

        session(['current_team' => $team_id]);

        return redirect()->intended('dashboard');
    }
}
