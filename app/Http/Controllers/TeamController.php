<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function showCreateTeam(){
        return view('dashboard.teams.create');
    }
}
