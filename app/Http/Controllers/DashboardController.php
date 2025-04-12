<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function showDashboard(){
        $current_team = session('current_team');

        if (!$current_team){
            return redirect()->route('dashboard.select-team');
        }

        return view('dashboard.index');
    }

    public function showSelectTeam()
    {
        $user = User::find(session('user_id'));
        $teams = $user->teams()->with('users')->get();

        return view('dashboard.select-team', [
            'teams' => $teams,
            'user' => $user,
        ]);
    }
}
