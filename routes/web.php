<?php

use App\Http\Controllers\EmailRegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PhoneAuthController;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Verify\V2\Service\VerificationList;



Route::middleware(['guest'])->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/login', function() {
        return view('auth.login');
    })->name('login');

    Route::get('auth/{provider}/redirect', [OAuthController::class, 'redirect'])
     ->where('provider', 'google|github|microsoft')->name('auth.oauth.redirect');

    Route::get('auth/{provider}/callback', [OAuthController::class, 'callback'])
        ->where('provider', 'google|github|microsoft');

    Route::get('/auth/phone', [PhoneAuthController::class, 'showPhoneForm'])->name('auth.phone');
    Route::get('/auth/phone/verify', [PhoneAuthController::class, 'showVerificationForm'])->name('auth.phone.verify');
    Route::get('/auth/phone/register', [PhoneAuthController::class, 'showRegistrationForm'])->name('auth.phone.register');
    Route::post('/auth/phone/register', [PhoneAuthController::class, 'completeRegistration'])->name('auth.phone.register.post');


    Route::get('/register', function() {
        return view('auth.register');
    })->name('register');
    Route::post('auth/register/email', [EmailRegistrationController::class, 'registerEmail'])->name('register.email');
    Route::get('/auth/register/verify', [EmailRegistrationController::class, 'showEmailVerification'])->name('auth.email-verification');
    Route::post('/auth/email/verify', [EmailRegistrationController::class, 'verifyEmail'])->name('auth.email.verify');
    Route::post('/auth/email/resend-verification', [EmailRegistrationController::class, 'resendVerificationCode'])->name('auth.email.resend-verification');
    Route::get('/auth/register/complete', [EmailRegistrationController::class, 'showRegistrationCompletion'])->name('auth.registration-completion');
    Route::get('');
});

Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::post('/auth/phone', [PhoneAuthController::class, 'sendCode'])->name('auth.phone.send');
    Route::post('/auth/phone/verify', [PhoneAuthController::class, 'verifyCode'])->name('auth.phone.verify.post');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        return view('dashboard.index');
    })->name('dashboard');
});

