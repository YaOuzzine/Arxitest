<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenOrSessionAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Case 1: User is already authenticated via session
        if (Auth::check()) {
            return $next($request);
        }

        // Case 2: For API or AJAX requests, check for token in Authorization header
        if ($request->expectsJson() || $request->ajax()) {
            // Let Sanctum handle it, will return 401 if token is invalid
            return $next($request);
        }

        // Case 3: For web requests, check if it's an API token request to be handled client-side
        // This is a web request that should load the page for client-side auth
        if ($request->is('/')) {
            return $next($request);
        }

        // If we get here, the user is not authenticated for a protected route
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return redirect()->route('login');
    }
}
