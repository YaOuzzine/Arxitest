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

            // Custom authentication logic since we're using password_hash
            Log::info("Looking for the user with email: " . $validated['email']);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                Log::warning("User not found: " . $validated['email']);
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Properly log user object as JSON
            error_log("Found user" . json_encode($user->toArray()));

            if (!Hash::check($validated['password'], $user->password_hash)) {
                error_log("Invalid password for user: " . $validated['email']);
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            Log::info("Creating auth token for user ID: " . $user->id);
            try {
                // Check if the user model has the HasApiTokens trait
                if (!method_exists($user, 'createToken')) {
                    error_log("User model is missing HasApiTokens trait");
                    throw new \Exception("User model is missing required trait for token creation");
                }

                // Check if personal_access_tokens table exists
                $tableExists = DB::getSchemaBuilder()->hasTable('personal_access_tokens');
                if (!$tableExists) {
                    error_log("personal_access_tokens table does not exist");
                    throw new \Exception("Database table for tokens does not exist");
                }

                $token = $user->createToken('auth_token')->plainTextToken;
                error_log("Token created successfully");

                Auth::login($user, $request->remember ?? false);
                error_log("Logged in successfully with token ". $token);
                // For the login response, consider not returning the full user object
                // to reduce response size and potential circular reference issues
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
                error_log("Token creation failed: " . $e->getMessage());
                error_log($e->getTraceAsString());

                // Return a more specific error message but don't expose internal details
                return response()->json([
                    'message' => 'Authentication system error. Please contact support.',
                    'error_code' => 'token_creation_failed'
                ], 500);
            }
        } catch (ValidationException $e) {
            Log::warning("Validation failed: " . json_encode($e->errors()));
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Login failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'message' => 'Login failed: An unexpected error occurred'
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
