<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase()
                        ->letters()
                        ->numbers()
                        ->symbols()
                ],
                'team_name' => 'required|string|max:255'
            ]);

            // Start transaction to create user and team
            DB::beginTransaction();

            try {
                // Create user with password_hash field
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password_hash' => Hash::make($validated['password']), // Using password_hash field
                    'role' => 'user'
                ]);

                // Create team
                $team = Team::create([
                    'name' => $validated['team_name'],
                    'description' => 'Team for ' . $validated['team_name']
                ]);

                // Associate user with team
                $user->teams()->attach($team->id);

                DB::commit();

                // Create token manually since we're not using the standard password field
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'user' => $user,
                    'team' => $team,
                    'access_token' => $token
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Registration failed: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Registration failed: ' . $e->getMessage(),
                    'error' => 'database_error'
                ], 500);
            }
        } catch (ValidationException $e) {
            // Return detailed validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error' => 'validation_error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => 'server_error'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            // This is the crucial part - we need to actually log the user in
            // for session authentication
            Auth::login($user, $request->remember ?? false);

            // Force the session to be saved immediately
            session()->save();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'access_token' => $token
            ]);
        } catch (\Exception $e) {
            Log::error("Login failed: " . $e->getMessage());
            return response()->json([
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
{
    try {
        Log::info("Starting logout process for user ID: " . ($request->user() ? $request->user()->id : 'Unknown'));

        // Revoke the token that was used to authenticate the current request
        if ($request->user() && $request->bearerToken()) {
            Log::info("Revoking access token for user");
            $request->user()->currentAccessToken()->delete();
        }

        // Logout of the web session
        Auth::guard('web')->logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        Log::info("User logged out successfully");

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        }

        return redirect('/login');
    } catch (\Exception $e) {
        Log::error("Logout failed: " . $e->getMessage());
        Log::error($e->getTraceAsString());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }

        return redirect('/login')->with('error', 'Logout failed');
    }
}

    public function me(Request $request)
    {
        try {
            return response()->json([
                'user' => $request->user()->load('teams')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve user profile: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve user profile'
            ], 500);
        }
    }
}
