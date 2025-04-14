<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;

class TeamController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the team creation form
     *
     * @return \Illuminate\View\View
     */
    public function showCreateTeam()
    {
        return view('dashboard.teams.create');
    }

    /**
     * Store a newly created team
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'logo' => 'nullable|image|max:2048', // max 2MB
            'invites' => 'nullable|json'
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

        // Create the team
        $team = new Team();
        $team->name = $request->name;
        $team->description = $request->description;
        $team->save();

        // Add the current user as owner
        $user = Auth::user();
        $team->users()->attach($user->id, ['team_role' => 'owner']);

        // Store team logo if provided
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('team-logos', 'public');
            $team->logo_path = $logoPath;
            $team->save();
        }

        // Process invites if present
        if ($request->has('invites')) {
            $invites = json_decode($request->invites, true);
            if (is_array($invites)) {
                foreach ($invites as $invite) {
                    // Check if user already exists
                    $invitedUser = User::where('email', $invite['email'])->first();

                    if ($invitedUser) {
                        // Add existing user directly to team
                        $team->users()->attach($invitedUser->id, [
                            'team_role' => $invite['role']
                        ]);
                    } else {
                        // Create a team invitation record
                        // In a real implementation, you would create an invitation and send an email
                        // This is simplified for the example
                    }
                }
            }
        }

        // Set current team in session
        session(['current_team' => $team->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Team created successfully',
                'team' => $team,
                'redirect' => route('dashboard')
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

        $processed = 0;
        $invites = [];

        foreach ($request->emails as $email) {
            // Check if user already exists
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                // Check if user is already a member
                if (!$team->users->contains($existingUser->id)) {
                    $team->users()->attach($existingUser->id, [
                        'team_role' => $request->role
                    ]);
                    $processed++;
                }
            } else {
                // Create invitation for new user
                // For now, we'll simulate this
                $invites[] = [
                    'email' => $email,
                    'role' => $request->role,
                    'token' => Str::random(32)
                ];

                // You would implement actual email sending here
                // Mail::to($email)->send(new TeamInvitation($team, $token));
                $processed++;
            }
        }

        // In a real app, you would save these invites to the database
        // TeamInvitation::insert($invites);

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
        $this->authorize('update', $team);

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
