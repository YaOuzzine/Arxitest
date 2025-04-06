<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthController;

Route::get('/', function () {
    return view('welcome');
});

// Route to SHOW the login form
Route::get('/login', function() {
    return view('auth.login');
})->name('login');

Route::get('/register', function() {
    return view('auth.login');
})->name('register');


Route::get('/session-test', function () {
    session(['foo' => 'bar']);

    return session()->all();
});


// routes/web.php

Route::get('auth/{provider}/redirect', [OAuthController::class, 'redirect'])
     ->where('provider', 'google|github|microsoft')->name('auth.oauth.redirect');

Route::get('auth/{provider}/callback', [OAuthController::class, 'callback'])
     ->where('provider', 'google|github|microsoft');


