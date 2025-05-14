<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebLoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function webLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only(['email', 'password']);
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $user = $request->user();

        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Email not verified. Please verify your email before logging in.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Check for pending invitations passed in URL or session
        $token = $request->query('invitation') ?? session('invitation_token');

        if ($token) {
            session(['invitation_token' => $token]);
            return redirect()->route('invitations.complete');
        }

        // Check if there are any pending invitations for this user
        $pendingInvitations = TeamInvitation::where('email', $user->email)
            ->where('expires_at', '>', now())
            ->first();

        if ($pendingInvitations) {
            session(['invitation_token' => $pendingInvitations->token]);
            return redirect()->route('invitations.complete');
        }

        return redirect()->intended('dashboard');
    }

    public function webLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function webUser(Request $request)
    {
        return view('auth.profile', ['user' => $request->user()]);
    }
}
