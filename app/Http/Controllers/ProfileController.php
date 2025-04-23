<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show()
    {
        $user = Auth::user();

        return view('dashboard.profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        $user = Auth::user();

        return view('dashboard.profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle avatar upload if provided
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $avatarPath;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'] ?? $user->phone_number;
        $user->save();

        return redirect()->route('dashboard.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the form for changing the password.
     */
    public function editPassword()
    {
        return view('dashboard.profile.password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = $validated['password']; // Uses mutator in User model
        $user->save();

        return redirect()->route('dashboard.profile.show')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Show the security settings page.
     */
    public function security()
    {
        $user = Auth::user();

        return view('dashboard.profile.security', [
            'user' => $user,
            'sessions' => [], // In a real app, you would fetch active sessions here
        ]);
    }

    /**
     * Show connected accounts and integration settings.
     */
    public function connections()
    {
        $user = Auth::user();

        $connectedAccounts = [
            'google' => !empty($user->google_id),
            'github' => !empty($user->github_id),
            'microsoft' => !empty($user->microsoft_id),
        ];

        return view('dashboard.profile.connections', [
            'user' => $user,
            'connectedAccounts' => $connectedAccounts,
        ]);
    }

    /**
     * Show notification preferences.
     */
    public function notifications()
    {
        $user = Auth::user();

        // In a real app, you'd fetch actual notification preferences
        $preferences = [
            'email_notifications' => true,
            'push_notifications' => false,
            'test_run_alerts' => true,
            'security_alerts' => true,
            'marketing_emails' => false,
        ];

        return view('dashboard.profile.notifications', [
            'user' => $user,
            'preferences' => $preferences,
        ]);
    }

    public function updateNotifications(Request $request)
{
    $user = Auth::user();

    // Validate the incoming request
    $validated = $request->validate([
        'email_notifications' => 'sometimes|boolean',
        'push_notifications' => 'sometimes|boolean',
        'test_run_alerts' => 'sometimes|boolean',
        'security_alerts' => 'sometimes|boolean',
        'marketing_emails' => 'sometimes|boolean',
        'notification_frequency' => 'sometimes|in:realtime,daily,weekly',
        'quiet_start' => 'sometimes|date_format:H:i',
        'quiet_end' => 'sometimes|date_format:H:i',
    ]);

    // In a real application, you would update the user's notification preferences
    // in a dedicated table/model. For now, we'll just store it in the session
    // for demonstration purposes

    // Example of how you might store these in a real app:
    // $user->notificationSettings()->update([
    //     'email_enabled' => $request->boolean('email_notifications', false),
    //     'push_enabled' => $request->boolean('push_notifications', false),
    //     'test_run_alerts' => $request->boolean('test_run_alerts', false),
    //     'security_alerts' => $request->boolean('security_alerts', true), // Security alerts often can't be disabled
    //     'marketing_emails' => $request->boolean('marketing_emails', false),
    //     'frequency' => $request->input('notification_frequency', 'realtime'),
    //     'quiet_hours_start' => $request->input('quiet_start'),
    //     'quiet_hours_end' => $request->input('quiet_end'),
    // ]);

    // For demo purposes, store in session
    session(['notification_preferences' => [
        'email_notifications' => $request->boolean('email_notifications', false),
        'push_notifications' => $request->boolean('push_notifications', false),
        'test_run_alerts' => $request->boolean('test_run_alerts', false),
        'security_alerts' => $request->boolean('security_alerts', true),
        'marketing_emails' => $request->boolean('marketing_emails', false),
        'frequency' => $request->input('notification_frequency', 'realtime'),
        'quiet_start' => $request->input('quiet_start'),
        'quiet_end' => $request->input('quiet_end'),
    ]]);

    return redirect()->route('dashboard.profile.notifications')
        ->with('success', 'Notification preferences updated successfully.');
}
}
