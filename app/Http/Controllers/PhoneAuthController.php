<?php

namespace App\Http\Controllers;

use App\Models\PhoneVerification;
use App\Models\User;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PhoneAuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Show phone verification form (for registration)
     */
    public function showPhoneForm()
    {
        return view('auth.phone');
    }

    /**
     * Send verification code to the phone
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^\+[1-9]\d{1,14}$/'], // E.164 format
        ]);

        $phone = $request->input('phone_number');

        // Generating a random OTP code
        $code = random_int(100000, 999999);

        // Calculating expiration, 5 minutes from now
        $expiresAt = Carbon::now()->addMinutes(5);

        // Storing the OTP in thje database
        PhoneVerification::create([
            'phone_number' => $phone,
            'code'         => $code,
            'expires_at'   => $expiresAt,
        ]);

        // Sending the OTP via SMS
        $sent = $this->smsService->sendVerificationCode($phone, $code);

        if (!$sent) {

            Log::error("Failed to send verification code to $phone");
        }

        // Saving phone number in session for verification
        session(['phone_number' => $phone]);

        return redirect()->route('auth.phone.verification')->with('status', 'Verification code sent to your phone.');
    }

    public function resendCode(Request $request){
        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^\+[1-9]\d{1,14}$/'], // E.164 format
        ]);

        $phone = $request->input('phone_number');

        // Generating a random OTP code
        $code = random_int(100000, 999999);

        // Calculating expiration, 5 minutes from now
        $expiresAt = Carbon::now()->addMinutes(5);

        // Storing the OTP in thje database
        PhoneVerification::create([
            'phone_number' => $phone,
            'code'         => $code,
            'expires_at'   => $expiresAt,
        ]);

        // Sending the OTP via SMS
        $sent = $this->smsService->sendVerificationCode($phone, $code);

        if (!$sent) {

            Log::error("Failed to send verification code to $phone");
        }

        // Saving phone number in session for verification
        session(['phone_number' => $phone]);


        return response()->json(['success' => true, 'message' => 'Verification code resent successfully!']);
    }

    /**
     * Show code verification form
     */
    public function showVerificationForm()
    {
        $phoneNumber = session('phone_number');
        if (!$phoneNumber) {
            return redirect()->route('auth.phone')->withErrors(['phone_number' => 'Please enter your phone number first.']);
        }

        return view('auth.phone-verification', ['phoneNumber' => $phoneNumber]);
    }

    /**
     * Verify the phone number and code
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'verification_code' => ['required', 'digits:6'],
        ]);

        $phone = session('phone_number');
        $code = $request->input('verification_code');

        if (!$phone) {
            return redirect()->route('auth.phone')->withErrors(['phone_number' => 'Please enter your phone number first.']);
        }

        // Fetch the latest valid code for this phone
        $verification = PhoneVerification::where('phone_number', $phone)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (!$verification) {
            return redirect()->route('auth.phone.verification')->withErrors(['verification_code' => 'Verification code expired or invalid. Please request a new one.']);
        }

        // Use constant-time comparison to prevent timing attacks
        if (!Hash::equals((string)$verification->code, (string)$code)) {
            return redirect()->route('auth.phone.verification')->withErrors(['verification_code' => 'Invalid verification code. Please try again.']);
        }

        // Mark the verification as used
        $verification->used = true;
        $verification->save();

        // User registration or login logic
        // Check if a user with this phone number already exists
        $user = User::where('phone_number', $phone)->first();

        if ($user) {
            // This is a login - update the verification status
            $user->phone_verified = true;
            $user->save();

            // Log the user in
            Auth::login($user);

            return redirect()->intended('/dashboard');
        } else {
            // This is a new registration - create the user
            // At this point you might want to collect additional information
            // or directly create a minimal user account

            // For now, redirect to a registration completion page
            session(['verified_phone' => $phone]);
            return redirect()->route('auth.phone.register');
        }
    }

    /**
     * Show the registration completion form for phone users
     */
    public function showRegistrationForm()
    {
        $verifiedPhone = session('verified_phone');
        if (!$verifiedPhone) {
            return redirect()->route('auth.phone')->withErrors(['phone_number' => 'Please verify your phone number first.']);
        }

        return view('auth.phone-register', ['phone_number' => $verifiedPhone]);
    }

    /**
     * Complete the phone-based registration
     */
    public function completeRegistration(Request $request)
    {
        $verifiedPhone = session('verified_phone');
        if (!$verifiedPhone) {
            return redirect()->route('auth.phone')->withErrors(['phone_number' => 'Please verify your phone number first.']);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email ?? null,
            'password_hash' => Hash::make($request->password),
            'phone_number' => $verifiedPhone,
            'phone_verified' => true,
        ]);

        // Log the user in
        Auth::login($user);

        // Clear session data
        session()->forget(['verified_phone', 'phone_number']);

        return redirect('/dashboard');
    }
}
