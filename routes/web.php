<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PhoneAuthController;
use Illuminate\Support\Facades\Auth;



Route::middleware(['guest'])->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/login', function() {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function() {
        return view('auth.register');
    })->name('register');

    Route::get('auth/{provider}/redirect', [OAuthController::class, 'redirect'])
     ->where('provider', 'google|github|microsoft')->name('auth.oauth.redirect');

    Route::get('auth/{provider}/callback', [OAuthController::class, 'callback'])
        ->where('provider', 'google|github|microsoft');

    Route::get('/auth/phone', [PhoneAuthController::class, 'showPhoneForm'])->name('auth.phone');
    Route::get('/auth/phone/verify', [PhoneAuthController::class, 'showVerificationForm'])->name('auth.phone.verify');
    Route::get('/auth/phone/register', [PhoneAuthController::class, 'showRegistrationForm'])->name('auth.phone.register');
    Route::post('/auth/phone/register', [PhoneAuthController::class, 'completeRegistration'])->name('auth.phone.register.post');
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


// Phone authentication routes
Route::middleware(['guest'])->group(function () {

});

