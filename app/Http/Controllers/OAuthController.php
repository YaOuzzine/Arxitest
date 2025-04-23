<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class OAuthController extends Controller
{
    public function redirect(string $provider)
    {
        // Redirect to the provider's OAuth page
        Auth::shouldUse('web');
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        // Handle OAuth callback and retrieve user info
        Auth::shouldUse('web');
        try {
            $oauthUser = Socialite::driver($provider)->user();
            // Find or create a corresponding user in our database
            $user = User::updateOrCreate(
                [$provider . '_id' => $oauthUser->getId()],  // e.g. github_id, google_id
                [
                    'name'  => $oauthUser->getName() ?? $oauthUser->getNickname(),
                    'email' => $oauthUser->getEmail(),

                ]
            );

            // Log the user into our application
            Auth::login($user, true);

            // Redirect to intended page or dashboard
            return redirect()->intended('/');
        } catch (\Exception $e) {
            // Log the full exception
            Log::error("OAuth callback error: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->route('login')
                ->withErrors(['oauth' => 'Authentication failed. Please try again.']);
        }
    }

    /**
     * Disconnect a third-party OAuth provider from the user's account.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect(string $provider)
    {
        // Validate the provider
        if (!in_array($provider, ['google', 'github', 'microsoft'])) {
            return redirect()->route('dashboard.profile.connections')
                ->with('error', 'Invalid provider specified.');
        }

        $user = Auth::user();

        // Make sure the user has another way to log in
        $hasEmail = !empty($user->email) && !empty($user->password_hash);
        $hasPhone = !empty($user->phone_number) && $user->phone_verified;
        $connectedProviders = collect(['google_id', 'github_id', 'microsoft_id'])
            ->filter(function ($field) use ($user, $provider) {
                $providerField = $provider . '_id';
                return $field !== $providerField && !empty($user->{$field});
            });

        // Prevent disconnecting if it's the only login method
        if (!$hasEmail && !$hasPhone && $connectedProviders->isEmpty()) {
            return redirect()->route('dashboard.profile.connections')
                ->with('error', 'Cannot disconnect your only login method. Please add another login method first.');
        }

        // Update the user record to remove the provider ID
        $providerField = $provider . '_id';
        $user->{$providerField} = null;
        $user->save();

        return redirect()->route('dashboard.profile.connections')
            ->with('success', ucfirst($provider) . ' account disconnected successfully.');
    }
}
