<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamInvitation;
use App\Mail\TeamInvitation as TeamInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use PgSql\Lob;

class TeamController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the teams.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Eager load team relationships to avoid N+1 query problem
        $teams = $user->teams()
            ->with(['users' => function($query) {
                $query->select('users.id', 'name', 'email');
            }])
            ->withCount(['projects', 'users'])
            ->get();

        // Add pivot data directly to users for frontend access
        $teams->each(function ($team) {
            $team->users->each(function ($user) {
                $user->role = $user->pivot->team_role;
            });
        });

        return view('dashboard.teams.index', [
            'teams' => $teams,
            'currentTeamId' => session('current_team')
        ]);
    }

    /**
     * Show the team creation form
     *
     * @return \Illuminate\View\View
     */
    public function showCreateTeam()
    {
        return view('dashboard.teams.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'logo' => 'nullable|image|max:2048',
            'invites' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            // ... error handling ...
             if ($request->expectsJson()) {
                 return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
             }
             return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create the team
        $team = new Team();
        $team->name = $request->name;
        $team->description = $request->description;
        $team->save();

        // Add the current user as owner
        $user = Auth::user();
        $team->users()->attach($user->id, ['team_role' => 'owner']); // Attach to DB

        // *** NEW: Re-authenticate the user to refresh their relationships in the session ***
        Auth::login($user); // This effectively refreshes the user model stored in the session state

        // Store team logo if provided
        if ($request->hasFile('logo')) {
            // ... logo handling ...
             $logoPath = $request->file('logo')->store('team-logos', 'public');
             $team->logo_path = $logoPath;
             $team->save();
        }

        // Process invites if present
        if ($request->filled('invites')) { // Use filled() for better check
            try {
                 $invites = json_decode($request->invites, true, 512, JSON_THROW_ON_ERROR);
                 if (is_array($invites)) {
                     $this->processTeamInvitations($team, $invites);
                 }
             } catch (\JsonException $e) {
                 // Handle potential JSON decoding errors if necessary
                 Log::error('Invalid JSON in team invites during creation: ' . $e->getMessage());
                 // Optionally return an error back to the user
             }
        }


        // Set current team in session
        session(['current_team' => $team->id]);

        // Explicitly save the session AFTER setting the team and re-logging in
        $request->session()->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Team created successfully',
                'team' => $team->load('users'), // Optionally load users for response
                'redirect' => route('dashboard') // Redirect to main dashboard after creation
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Team created successfully');
    }

    /**
     * Show team details
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $team = Team::with(['users', 'projects.testSuites.testCases'])->findOrFail($id);

        // Check if user is authorized to view team
        $this->authorize('view', $team);

        return view('dashboard.teams.show', compact('team'));
    }

    /**
     * Show team edit form
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $team = Team::findOrFail($id);

        // Check if user is authorized to edit team
        $this->authorize('update', $team);

        return view('dashboard.teams.edit', compact('team'));
    }

    /**
     * Update team details
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        // Check if user is authorized to update team
        $this->authorize('update', $team);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'logo' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update team details
        $team->name = $request->name;
        $team->description = $request->description;

        // Handle logo updates
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($team->logo_path) {
                Storage::disk('public')->delete($team->logo_path);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('team-logos', 'public');
            $team->logo_path = $logoPath;
        } elseif ($request->boolean('remove_logo') && $team->logo_path) {
            // Remove logo if requested
            Storage::disk('public')->delete($team->logo_path);
            $team->logo_path = null;
        }

        $team->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Team updated successfully',
                'team' => $team
            ]);
        }

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Team updated successfully');
    }

    /**
     * Process team invitations
     *
     * @param Team $team
     * @param array $invites
     * @return int Number of invitations sent
     */
    private function processTeamInvitations(Team $team, array $invites): int
    {
        $processed = 0;
        $user = Auth::user();

        foreach ($invites as $invite) {
            $email = $invite['email'];
            $role = $invite['role'];

            // Skip invalid emails
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Check if user already exists
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                // Check if user is already a member
                if (!$team->users->contains($existingUser->id)) {
                    $team->users()->attach($existingUser->id, [
                        'team_role' => $role
                    ]);
                    $processed++;
                }
            } else {
                // Create invitation for new user
                $token = Str::random(64);
                $expiresAt = now()->addDays(7);

                // Check if an invitation already exists
                $existingInvitation = TeamInvitation::where('team_id', $team->id)
                    ->where('email', $email)
                    ->first();

                if ($existingInvitation) {
                    // Update existing invitation
                    $existingInvitation->update([
                        'role' => $role,
                        'token' => $token,
                        'expires_at' => $expiresAt
                    ]);
                } else {
                    // Create new invitation
                    TeamInvitation::create([
                        'team_id' => $team->id,
                        'email' => $email,
                        'role' => $role,
                        'token' => $token,
                        'expires_at' => $expiresAt
                    ]);
                }

                // Send invitation email
                Mail::to($email)->send(
                    new TeamInvitationMail($team, $user->name, $token, $role)
                );

                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Send invitations to join the team
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInvitations(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        // Check if user is authorized to invite members
        $this->authorize('update', $team);

        $validator = Validator::make($request->all(), [
            'emails' => 'required|array',
            'emails.*' => 'required|email',
            'role' => 'required|in:member,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 422);
        }

        // Format invitations for processing
        $invites = [];
        foreach ($request->emails as $email) {
            $invites[] = [
                'email' => $email,
                'role' => $request->role
            ];
        }

        $processed = $this->processTeamInvitations($team, $invites);

        return response()->json([
            'success' => true,
            'message' => "{$processed} invitation(s) sent successfully"
        ]);
    }

    /**
     * Update a team member's role
     *
     * @param Request $request
     * @param string $teamId
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMemberRole(Request $request, $teamId, $userId)
    {
        $team = Team::findOrFail($teamId);
        $user = User::findOrFail($userId);

        // Check if user is authorized to manage members
        $this->authorize('update', $team);

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:member,admin,owner'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is a member of the team
        if (!$team->users->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this team'
            ], 404);
        }

        // Update role
        $team->users()->updateExistingPivot($user->id, [
            'team_role' => $request->role
        ]);

        return response()->json([
            'success' => true,
            'message' => "Member role updated successfully"
        ]);
    }

    /**
     * Remove a member from the team
     *
     * @param string $teamId
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeMember($teamId, $userId)
    {
        $team = Team::findOrFail($teamId);
        $user = User::findOrFail($userId);

        // Check if user is authorized to manage members
        $this->authorize('update', $team);

        // Check if user is a member of the team
        if (!$team->users->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this team'
            ], 404);
        }

        // Can't remove yourself
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot remove yourself from the team'
            ], 403);
        }

        // Remove member
        $team->users()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => "Member removed successfully"
        ]);
    }

    /**
     * Delete the team
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $team = Team::findOrFail($id);

        // Check if user is authorized to delete team
        $this->authorize('delete', $team);

        // Clear current team from session if it's this one
        if (session('current_team') == $id) {
            session()->forget('current_team');
        }

        // Delete logo if exists
        if ($team->logo_path) {
            Storage::disk('public')->delete($team->logo_path);
        }

        // Delete team (cascade should handle related records)
        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully',
            'redirect' => route('dashboard.select-team')
        ]);
    }
}
