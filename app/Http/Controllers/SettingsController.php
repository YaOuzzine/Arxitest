<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Show the main settings page.
     * This page could potentially list various settings categories (team, billing, etc.)
     * or be a starting point before navigating to sub-sections.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            // This middleware should catch this, but as a safeguard
            return redirect()->route('login');
        }

        $currentTeamId = session('current_team');
         if (!$currentTeamId) {
             // Redirect to team selection if no team is set
             return redirect()->route('dashboard.select-team')->with('error', 'Please select a team first.');
         }

         // Optional: Load team information if needed for the settings page content
         $team = $user->teams()->find($currentTeamId);

         if (!$team) {
             Log::warning('Settings access failed: Session team invalid or user not member.', [
                 'user_id' => $user->id, 'session_current_team' => $currentTeamId
             ]);
             session()->forget('current_team');
             return redirect()->route('dashboard.select-team')->with('error', 'Invalid team selection. Please re-select.');
         }

        return view('dashboard.settings.index', [
            'user' => $user,
            'team' => $team, // Pass team if needed for context
        ]);
    }

    // You can add other methods here later for specific setting types (e.g., updateTeamSettings, updateBillingSettings)
}
