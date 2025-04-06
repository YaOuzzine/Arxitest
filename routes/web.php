<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthController;
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

});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        return view('dashboard.index');
    })->name('dashboard');
});


