<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamInvitation;
use App\Mail\TeamInvitation as TeamInvitationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TeamService
{
    /**
     * Create a new team, attach owner, handle logo + invites, set session.
     */
    public function create(array $data): Team
    {
        $team = Team::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        // Attach owner
        $user = Auth::user();
        $team->users()->attach($user->id, ['team_role' => 'owner']);
        Auth::login($user); // refresh session

        // Logo upload
        if (! empty($data['logo']) && $data['logo']->isValid()) {
            $path = $data['logo']->store('team-logos', 'public');
            $team->update(['logo_path' => $path]);
        }

        // Process invites JSON
        if (! empty($data['invites'])) {
            try {
                $invites = json_decode($data['invites'], true, 512, JSON_THROW_ON_ERROR);
                $this->processInvitations($team, $invites);
            } catch (\JsonException $e) {
                Log::error('Invalid team invites JSON: ' . $e->getMessage());
            }
        }

        // Persist current team in session
        session(['current_team' => $team->id]);
        session()->save();

        return $team;
    }

    /**
     * Update an existing team’s fields, logo add/remove.
     */
    public function update(Team $team, array $data): Team
    {
        $team->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (! empty($data['logo']) && $data['logo']->isValid()) {
            // delete old
            if ($team->logo_path) {
                Storage::disk('public')->delete($team->logo_path);
            }
            $path = $data['logo']->store('team-logos', 'public');
            $team->update(['logo_path' => $path]);
        } elseif (! empty($data['remove_logo']) && $team->logo_path) {
            Storage::disk('public')->delete($team->logo_path);
            $team->update(['logo_path' => null]);
        }

        return $team;
    }

    /**
     * Delete a team, clear session, remove logo.
     */
    public function delete(Team $team): void
    {
        if (session('current_team') == $team->id) {
            session()->forget('current_team');
        }
        if ($team->logo_path) {
            Storage::disk('public')->delete($team->logo_path);
        }
        $team->delete();
    }

    /**
     * Shared invite‐processing logic used in create().
     */
    private function processInvitations(Team $team, array $invites): int
    {
        $count = 0;
        $owner = Auth::user();

        foreach ($invites as $invite) {
            $email = $invite['email'];
            $role  = $invite['role'];
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $existing = User::where('email', $email)->first();
            if ($existing) {
                if (! $team->users->contains($existing->id)) {
                    $team->users()->attach($existing->id, ['team_role' => $role]);
                    $count++;
                }
            } else {
                $token     = Str::random(64);
                $expiresAt = now()->addDays(7);

                // update or create invitation
                TeamInvitation::updateOrCreate(
                    ['team_id' => $team->id, 'email' => $email],
                    ['role' => $role, 'token' => $token, 'expires_at' => $expiresAt]
                );

                Mail::to($email)
                    ->send(new TeamInvitationMail($team, $owner->name, $token, $role));

                $count++;
            }
        }

        return $count;
    }
}
