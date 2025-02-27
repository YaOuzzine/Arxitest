<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\TestScript;

class AuthDiagnoseController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $request)
    {
        // Check if authenticated via session
        $isAuthenticated = Auth::check();

        Log::info("Home page accessed. Auth status: " . ($isAuthenticated ? 'Authenticated' : 'Not authenticated'));

        if (!$isAuthenticated) {
            Log::info("User not authenticated via session, checking for token");

            // If not authenticated via session, check if this is an API request with a token
            if ($request->expectsJson() || $request->wantsJson() || $request->hasHeader('Authorization')) {
                // This is an API call, let Sanctum handle auth
                if (!$request->user()) {
                    Log::info("API request without valid token");
                    return response()->json(['authenticated' => false], 401);
                }

                // User is authenticated via token
                Log::info("User authenticated via token");
                return response()->json([
                    'authenticated' => true,
                    'user' => $request->user()
                ]);
            }

            // If it's a web request but not authenticated, and not an API call, show the debug/home page
            // The client-side JS in home.blade.php will handle checking for a token in localStorage
            Log::info("Regular web request, not authenticated. Serving home view.");
        } else {
            Log::info("User authenticated via session: " . Auth::id());
        }

        // For web requests or if authenticated
        try {
            $testScripts = [];

            if ($isAuthenticated) {
                // Fetch test scripts
                $testScripts = TestScript::with('creator')->get();
                Log::info("Fetched " . count($testScripts) . " test scripts");
            }

            return view('auth-diagnose', [
                'testScripts' => $testScripts,
                'isAuthenticated' => $isAuthenticated
            ]);
        } catch (\Exception $e) {
            Log::error("Error in HomeController: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Server error'], 500);
            }

            return view('auth-diagnose', [
                'testScripts' => [],
                'isAuthenticated' => $isAuthenticated,
                'error' => 'Could not load test scripts'
            ]);
        }
    }
}
