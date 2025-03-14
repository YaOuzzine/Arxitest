<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Display a listing of the user's teams
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $teams = $user->teams;

        return view('teams.index', compact('teams'));
    }

    /**
     * Show the form for creating a new team or joining an existing one
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        $userTeams = $user->teams;

        return view('teams.create', compact('userTeams'));
    }

    /**
     * Store a newly created team in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Create the team
            $team = Team::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            // Associate current user with the team
            $team->users()->attach(Auth::id());

            // Create a default free trial subscription for the team
            Subscription::create([
                'team_id' => $team->id,
                'plan_type' => 'free_trial',
                'max_containers' => 1,
                'retention_days' => 30,
                'max_parallel_runs' => 1,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('teams.show', $team)
                ->with('success', 'Team created successfully! You can now start adding projects.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Team creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Team creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Join an existing team via invitation code
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'invitation_code' => 'required|string|size:8',
        ]);

        try {
            // Find the team by invitation code (stored in the settings JSON field)
            $team = Team::where(DB::raw("(settings->>'invitation_code')"), $validated['invitation_code'])->first();

            if (!$team) {
                return redirect()->back()
                    ->with('error', 'Invalid invitation code. Please check and try again.')
                    ->withInput();
            }

            // Check if user is already a member of this team
            $userIsTeamMember = $team->users()->where('user_id', Auth::id())->exists();

            if ($userIsTeamMember) {
                return redirect()->route('teams.show', $team)
                    ->with('info', 'You are already a member of this team.');
            }

            // Associate user with the team
            $team->users()->attach(Auth::id());

            return redirect()->route('teams.show', $team)
                ->with('success', 'You have successfully joined the team!');

        } catch (\Exception $e) {
            Log::error('Team join failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to join team: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified team
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\View\View
     */
    public function show(Team $team)
    {
        // Authorization: ensure the user is a member of this team
        if (Gate::denies('view', $team)) {
            abort(403, 'Unauthorized action.');
        }

        // Load relationships for the view
        $team->load(['users', 'projects', 'subscriptions' => function($query) {
            $query->where('is_active', true)->latest();
        }]);

        $teamMembers = $team->users;
        $projects = $team->projects;
        $subscription = $team->subscriptions->first();

        return view('teams.show', compact('team', 'teamMembers', 'projects', 'subscription'));
    }

    /**
     * Show the form for editing the specified team
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\View\View
     */
    public function edit(Team $team)
    {
        // Authorization: ensure the user can edit this team
        if (Gate::denies('update', $team)) {
            abort(403, 'Unauthorized action.');
        }

        return view('teams.edit', compact('team'));
    }

    /**
     * Update the specified team in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Team $team)
    {
        // Authorization: ensure the user can update this team
        if (Gate::denies('update', $team)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $team->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            return redirect()->route('teams.show', $team)
                ->with('success', 'Team details updated successfully!');

        } catch (\Exception $e) {
            Log::error('Team update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update team: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Generate a new invitation code for the team
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateInviteCode(Team $team)
    {
        // Authorization: ensure the user can update this team
        if (Gate::denies('update', $team)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Generate a random 8-character invitation code
            $invitationCode = strtoupper(Str::random(8));

            // Update team settings with the new invitation code
            $settings = $team->settings ?? [];
            $settings['invitation_code'] = $invitationCode;
            $settings['invitation_generated_at'] = now()->toISOString();

            $team->update(['settings' => $settings]);

            return redirect()->route('teams.show', $team)
                ->with('success', 'New invitation code generated: ' . $invitationCode);

        } catch (\Exception $e) {
            Log::error('Invitation code generation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to generate invitation code: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified team from storage
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Team $team)
    {
        // Authorization: ensure the user can delete this team
        if (Gate::denies('delete', $team)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Delete all associated records
            // (The database has cascade deletion, but we're being explicit here)
            $team->subscriptions()->delete();
            $team->users()->detach();

            // Projects will cascade delete test suites, scripts, etc.
            $team->projects()->delete();

            // Finally delete the team
            $team->delete();

            DB::commit();

            return redirect()->route('teams.index')
                ->with('success', 'Team deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Team deletion failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to delete team: ' . $e->getMessage());
        }
    }
}
