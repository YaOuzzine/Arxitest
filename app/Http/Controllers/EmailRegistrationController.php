<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailRegistrationController extends Controller
{
    public function registerEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users']
        ]);

        $email = $request->email;

        // Generate a random 6-digit code
        $code = random_int(100000, 999999);

        // Store email and code in session
        $request->session()->put('verification', [
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10) // Code expires after 10 minutes
        ]);

        // Send email with verification code
        Mail::to($email)->send(new EmailVerification($code));

        return redirect()->route('auth.email-verification');
    }

    public function showEmailVerification()
    {
        return view('auth.email-verification');
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'verification_code' => ['required', 'digits:6'],
        ]);

        $verification = $request->session()->get('verification');

        if (!$verification || now()->isAfter($verification['expires_at'])){
            return redirect()->route('auth.email-verification')
                ->withErrors(['verification_code' => 'Verification code has expired. Please request a new one.']);
        }

        if ($request->verification_code != $verification['code']) {
            return redirect()->route('auth.email-verification')
                ->withErrors(['verification_code' => 'Invalid verification code.']);
        }

        $request->session()->put('verified_email', $verification['email']);

        return redirect()->route('auth.registration-completion');
    }

    public function resendVerificationCode(Request $request){
        $verification = $request->session()->get('verification');

        $email = $verification['email'];

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found in session'
            ], 400);
        }

        $code = random_int(100000, 999999);

        $request->session()->put('verification', [
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10)
        ]);

        try {
            Mail::to($email)->send(new EmailVerification($code));

            return response()->json([
                'success' => true,
                'message' => 'Verification code has been resent'
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to send verification email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code'
            ], 500);
        }
    }

    public function showRegistrationCompletion()
    {
        return view('auth.complete-registration');
    }

    public function completeRegistration(Request $request){
        $verifiedEmail = $request->session()->get('verified_email');

        $user = User::create([
            'name' => $request->username,
            'email' => $verifiedEmail,
            'password_hash' => Hash::make($request->password),
            'email_verified_at' => now()
        ]);

        $request->session()->forget(['verified_email', 'verifications']);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
