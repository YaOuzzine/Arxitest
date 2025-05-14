<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;
use App\Models\Container;
use App\Models\TeamInvitation; // Import TeamInvitation
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Import Carbon

class DashboardLayoutComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = Auth::user();
        $activeSubscription = null;
        $activeContainerCount = 0;
        $team = null;
        $notifications = collect(); // Initialize as empty collection
        $hasUnreadNotifications = false; // Initialize
        $pendingInvitationCount = 0; // Initialize

        if ($user) {
            $currentTeamId = session('current_team');
            if ($currentTeamId) {
                $team = $user->teams()
                    ->with([
                        'subscriptions' => function ($query) {
                            $query->where('is_active', true)->latest('created_at')->limit(1);
                        },
                        'projects:id,team_id' // Only select IDs needed
                    ])
                    ->find($currentTeamId);

                if ($team) {
                    $activeSubscription = $team->subscriptions->first();
                    $projectIds = $team->projects->pluck('id');
                    $activeContainerCount = $this->getActiveContainerCount($projectIds);
                } else {
                    Log::warning("DashboardLayoutComposer: Team not found or user not member.", ['user_id' => $user->id, 'team_id' => $currentTeamId]);
                }
            }

            // Fetch Pending Invitations Count (for the badge)
            $pendingInvitationCount = TeamInvitation::where('email', $user->email)
                ->where('expires_at', '>', now())
                ->count();

            // --- Fetch Notifications (Placeholder - Replace with your logic) ---
            // Example: Fetch latest 5 notifications for the user
            // $notifications = $user->notifications()->latest()->limit(5)->get()->map(function ($notification) {
            //     return [
            //         'id' => $notification->id,
            //         'title' => $notification->data['title'] ?? 'Notification',
            //         'message' => $notification->data['message'] ?? '',
            //         'url' => $notification->data['url'] ?? '#',
            //         'read' => !is_null($notification->read_at),
            //         'time_ago' => Carbon::parse($notification->created_at)->diffForHumans(),
            //     ];
            // });
            // $hasUnreadNotifications = $user->unreadNotifications()->exists();

            // --- Placeholder Notifications Data ---
            $notifications = collect([
                ['id' => 1, 'title' => 'Execution Failed', 'message' => 'Login tests failed on Prod.', 'time_ago' => '15m ago', 'url' => '#', 'read' => false],
                ['id' => 2, 'title' => 'New Member', 'message' => "Jane Doe joined 'Frontend Team'.", 'time_ago' => '2h ago', 'url' => '#', 'read' => true],
            ]);
            $hasUnreadNotifications = $notifications->where('read', false)->isNotEmpty();
            // --- End Placeholder ---

        }

        $view->with('activeSubscription', $activeSubscription);
        $view->with('activeContainerCount', $activeContainerCount);
        $view->with('currentTeamNameForLayout', $team->name ?? null);
        $view->with('layoutNotifications', $notifications); // Pass notifications
        $view->with('layoutHasUnreadNotifications', $hasUnreadNotifications); // Pass unread status
        $view->with('pendingInvitationCount', $pendingInvitationCount); // Pass invitation count

        if (Auth::check()) {
            $pendingInvitationsCount = TeamInvitation::where('email', Auth::user()->email)
                ->where('expires_at', '>', now())
                ->count();

            $view->with('pendingInvitationsCount', $pendingInvitationsCount);
        }
    }

    /**
     * Calculate the count of currently active containers for given projects.
     *
     * @param \Illuminate\Support\Collection $projectIds
     * @return int
     */
    private function getActiveContainerCount($projectIds): int
    {
        if ($projectIds->isEmpty()) {
            return 0;
        }

        try {
            // Find executions for the team's projects that might be running
            $runningExecutionIds = DB::table('test_executions')
                ->join('test_scripts', 'test_executions.script_id', '=', 'test_scripts.id')
                ->join('test_cases', 'test_scripts.test_case_id', '=', 'test_cases.id')
                ->join('test_suites', 'test_cases.suite_id', '=', 'test_suites.id')
                ->leftJoin('execution_statuses', 'test_executions.status_id', '=', 'execution_statuses.id')
                ->whereIn('test_suites.project_id', $projectIds)
                ->whereIn(DB::raw('lower(execution_statuses.name)'), ['running', 'pending', 'queued']) // Case-insensitive
                ->pluck('test_executions.id');

            if ($runningExecutionIds->isEmpty()) {
                return 0;
            }

            // Count containers linked to those executions with an active status
            return Container::whereIn('execution_id', $runningExecutionIds)
                ->whereRaw('lower(status) = ?', ['running']) // Adjust status name if needed
                ->count();
        } catch (\Exception $e) {
            Log::error("Error calculating active container count: " . $e->getMessage());
            return 0; // Return 0 on error
        }
    }
}
