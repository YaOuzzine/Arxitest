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
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Services\TeamService;
use App\Traits\JsonResponse;
use PgSql\Lob;

class TeamController extends Controller
{
    use AuthorizesRequests, JsonResponse;

    protected TeamService $teams;

    public function __construct(TeamService $teams)
    {

        $this->teams = $teams;
    }

    public function index()
    {
        $user = Auth::user();
        $teams = $user->teams()
            ->with(['users' => fn($q) => $q->select('users.id','name','email')])
            ->withCount(['projects','users'])
            ->get()
            ->each(fn($team) => $team->users->each(fn($u) => $u->role = $u->pivot->team_role));

        return view('dashboard.teams.index', [
            'teams'         => $teams,
            'currentTeamId' => session('current_team'),
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


    public function store(StoreTeamRequest $request)
    {
        $team = $this->teams->create($request->validated());

        if ($request->expectsJson()) {
            return $this->successResponse([
                'team'     => $team->load('users'),
                'redirect' => route('dashboard'),
            ], 'Team created successfully');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Team created successfully');
    }

    /**
     * Show team details
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show(Team $team)
    {
        $team->load(['users','projects.testSuites.testCases']);
        return view('dashboard.teams.show', ['team' => $team]);
    }

    /**
     * Show team edit form
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit(Team $team)
    {
        // $this->authorize('update', $team);
        return view('dashboard.teams.edit', compact('team'));
    }

    /**
     * Update team details
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $team = $this->teams->update($team, $request->validated());

        if ($request->expectsJson()) {
            return $this->successResponse([
                'team' => $team,
            ], 'Team updated successfully');
        }

        return redirect()->route('teams.show', $team)
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
            return $this->validationErrorResponse($validator, 'Invalid input');
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

        return $this->successResponse([], "{$processed} invitation(s) sent successfully");
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
            return $this->validationErrorResponse($validator, 'Invalid role');
        }


        // Check if user is a member of the team
        if (!$team->users->contains($user->id)) {
            return $this->errorResponse('User is not a member of this team', 404);
        }

        // Update role
        $team->users()->updateExistingPivot($user->id, [
            'team_role' => $request->role
        ]);

        return $this->successResponse([], "Member role updated successfully");
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
            return $this->errorResponse('User is not a member of this team', 404);
        }

        // Can't remove yourself
        if ($user->id === Auth::id()) {
            return $this->errorResponse('You cannot remove yourself from the team', 403);
        }

        // Remove member
        $team->users()->detach($user->id);

        return $this->successResponse([], "Member removed successfully");
    }

    /**
     * Delete the team
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Team $team)
    {
        $this->authorize('delete', $team);
        $this->teams->delete($team);

        if ($request->expectsJson()) {
            return $this->successResponse([
                'redirect' => route('dashboard.select-team'),
            ], 'Team deleted successfully');
        }

        return redirect()->route('dashboard.select-team')
            ->with('success', 'Team deleted successfully');
    }
}
