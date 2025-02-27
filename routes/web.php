<?php

use App\Http\Controllers\AuthDiagnoseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TestExecutionController;
use App\Http\Controllers\TestScriptController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\JiraStoryController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/auth-diagnose', [AuthDiagnoseController::class, 'index'])->name('auth');
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/overview', function () {
    return view('dashboard.overview');
})->name('overview');

Route::get('/pricing', function () {
    return view('dashboard.pricing');
})->name('pricing');

Route::get('/privacy', function () {
    return view('dashboard.privacy');
})->name('privacy');

Route::get('/faq', function () {
    return view('dashboard.faq');
})->name('faq');

// Guest routes (only accessible when NOT logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::get('/password/reset', function () {
        return view('auth.passwords.email');
    })->name('password.request');

    Route::get('/', function () {
        return redirect()->route('login');
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Inbox routes
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::get('/inbox/search', [InboxController::class, 'search'])->name('inbox.search');

    // Auth routes that require being logged in
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    // Add other protected routes here...
});

Route::middleware(['auth'])->group(function () {
    // Home route
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Inbox route
    Route::get('/inbox', function () {
        return view('inbox');
    })->name('inbox');

    // Project routes
    Route::resource('projects', ProjectController::class);

    // Test script routes
    Route::resource('test-scripts', TestScriptController::class);

    // Test execution routes
    Route::resource('test-executions', TestExecutionController::class);

    // Integration routes
    Route::resource('integrations', IntegrationController::class);

    // Jira story routes
    Route::resource('jira-stories', JiraStoryController::class);

    // Subscription routes
    Route::resource('subscriptions', SubscriptionController::class);
});
