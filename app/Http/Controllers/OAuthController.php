<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class OAuthController extends Controller
{
    public function redirect(string $provider){
        // Redirect to the provider's OAuth page
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        // Handle OAuth callback and retrieve user info
        $oauthUser = Socialite::driver($provider)->user();

        // Find or create a corresponding user in our database
        $user = User::updateOrCreate(
            [ $provider . '_id' => $oauthUser->getId() ],  // e.g. github_id, google_id
            [
                'name'  => $oauthUser->getName() ?? $oauthUser->getNickname(),
                'email' => $oauthUser->getEmail(),

            ]
        );

        // Log the user into our application
        Auth::login($user, true);

        // Redirect to intended page or dashboard
        return redirect()->intended('/');
    }
}
