<?php

use App\Http\Controllers\AuthDiagnoseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TestExecutionController;
use App\Http\Controllers\TestScriptController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TestSuiteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\JiraAuthController;
use App\Http\Controllers\JiraDataController;

Route::get('/auth-diagnose', [AuthDiagnoseController::class, 'index'])->name('auth');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jira/oauth', [JiraAuthController::class, 'redirectToJira'])->name('jira.oauth');
Route::get('/jira/callback', [JiraAuthController::class, 'handleCallback'])->name('jira.callback');
Route::get('/jira/import', [JiraDataController::class, 'index'])->name('jira.import');
Route::post('/jira/import', [JiraDataController::class, 'importData'])->name('jira.import.post');

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

     // Resource routes
    Route::resource('projects', ProjectController::class);
    Route::resource('test-scripts', TestScriptController::class);
    Route::resource('test-executions', TestExecutionController::class);
    Route::resource('integrations', IntegrationController::class);


    // Additional custom routes
    Route::get('test-scripts/{testScript}/generate', [TestScriptController::class, 'generate'])
        ->name('test-scripts.generate');
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

    // Version History
    Route::get('test-scripts/{testScript}/versions', [TestScriptController::class, 'versionHistory'])
    ->name('test-scripts.versions');

    // Restore Version
    Route::put('test-scripts/{testScript}/versions/{version}/restore', [TestScriptController::class, 'restoreVersion'])
    ->name('test-scripts.restore-version');

    // Basic resource routes
    Route::resource('test-suites', TestSuiteController::class);

    // Special routes for test suites
    Route::get('test-suites/{testSuite}/export', [TestSuiteController::class, 'export'])
        ->name('test-suites.export');

    Route::get('test-suites/import/form', [TestSuiteController::class, 'importForm'])
        ->name('test-suites.import-form');

    Route::post('test-suites/import', [TestSuiteController::class, 'import'])
        ->name('test-suites.import');

    Route::post('test-suites/{testSuite}/run-all', [TestSuiteController::class, 'runAllTests'])
        ->name('test-suites.run-all');

    Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');

    // Jira specific routes
    Route::post('/integrations/jira/disconnect', [IntegrationController::class, 'disconnectJira'])->name('integrations.jira.disconnect');
    Route::post('/integrations/jira/reconnect', [IntegrationController::class, 'reconnectJira'])->name('integrations.jira.reconnect');

    // API Keys management (these would normally use API routes)
    Route::post('/integrations/api-keys', [IntegrationController::class, 'generateApiKey'])->name('integrations.api-keys.generate');
    Route::delete('/integrations/api-keys/{id}', [IntegrationController::class, 'deleteApiKey'])->name('integrations.api-keys.delete');

    // Webhook management
    Route::post('/integrations/webhooks', [IntegrationController::class, 'createWebhook'])->name('integrations.webhooks.create');
    Route::delete('/integrations/webhooks/{id}', [IntegrationController::class, 'deleteWebhook'])->name('integrations.webhooks.delete');

    // Project routes
    Route::resource('projects', ProjectController::class);

    // Test script routes
    Route::resource('test-scripts', TestScriptController::class);

    // Test execution routes
    Route::resource('test-executions', TestExecutionController::class);

    // Integration routes
    Route::resource('integrations', IntegrationController::class);

    // Team routes
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::post('/teams/join', [TeamController::class, 'join'])->name('teams.join');
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('/teams/{team}/generate-invite-code', [TeamController::class, 'generateInviteCode'])->name('teams.generateInviteCode');

});


Route::get('/debug-auth', function () {
    return [
        'session_id' => session()->getId(),
        'auth_check' => Auth::check(),
        'auth_id' => Auth::id(),
        'auth_user' => Auth::user(),
        'cookies' => request()->cookies->all(),
        'session' => session()->all()
    ];
});

Route::get('/test-login/{userId}', function ($userId) {
    $user = \App\Models\User::findOrFail($userId);
    Auth::login($user);
    return redirect('/');
});
