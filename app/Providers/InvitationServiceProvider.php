<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\TeamInvitation;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class InvitationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share pending invitations count with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $pendingInvitationsCount = TeamInvitation::where('email', Auth::user()->email)
                    ->where('expires_at', '>', now())
                    ->count();

                $view->with('pendingInvitationsCount', $pendingInvitationsCount);
            }
        });

        // Add a method to the Team model to check for pending invitations
        Team::macro('hasPendingInvitationFor', function ($email) {
            return TeamInvitation::where('team_id', $this->id)
                ->where('email', $email)
                ->where('expires_at', '>', now())
                ->exists();
        });
    }
}
