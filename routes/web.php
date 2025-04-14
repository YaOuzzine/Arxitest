<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailRegistrationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PhoneAuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WebLoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC/GUEST ROUTES (Web Middleware)
|--------------------------------------------------------------------------
| Routes for guests visiting the main site: Login Forms, OAuth, Phone,
| Registration (Email & Phone). Applies 'guest' and 'web' middleware.
| No authentication required for these.
*/

Route::middleware(['guest', 'web'])->group(function () {

    // --- LANDING PAGE ---
    Route::get('/', function () {
        return view('welcome');
    });

    // --- WEB LOGIN FORM & SUBMIT ---
    Route::get('/login', [WebLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebLoginController::class, 'webLogin'])->name('login');

    // --- OAUTH (Google/Github/Microsoft) ---
    Route::get('auth/{provider}/redirect', [OAuthController::class, 'redirect'])
        ->where('provider', 'google|github|microsoft')
        ->name('auth.oauth.redirect');

    Route::get('auth/{provider}/callback', [OAuthController::class, 'callback'])
        ->where('provider', 'google|github|microsoft');

    // --- PHONE AUTH ROUTES ---
    Route::get('/auth/phone', [PhoneAuthController::class, 'showPhoneForm'])->name('auth.phone');
    Route::get('/auth/phone/verification', [PhoneAuthController::class, 'showVerificationForm'])->name('auth.phone.verification');
    Route::post('/auth/phone/verify', [PhoneAuthController::class, 'verifyCode'])->name('auth.phone.verify');
    Route::get('/auth/phone/register', [PhoneAuthController::class, 'showRegistrationForm'])->name('auth.phone.register');
    Route::post('/auth/phone/register', [PhoneAuthController::class, 'completeRegistration'])->name('auth.phone.register.post');

    // --- EMAIL REGISTRATION & EMAIL VERIFICATION ROUTES ---
    Route::get('/register', function() {
        return view('auth.register');
    })->name('register');

    Route::post('auth/register/email', [EmailRegistrationController::class, 'registerEmail'])->name('register.email');
    Route::get('/auth/register/verify', [EmailRegistrationController::class, 'showEmailVerification'])->name('auth.email-verification');
    Route::post('/auth/register/verify', [EmailRegistrationController::class, 'verifyEmail'])->name('auth.email.verify');
    Route::post('/auth/email/resend', [EmailRegistrationController::class, 'resendVerificationCode'])->name('auth.email.resend-verification');
    Route::get('/auth/register/complete', [EmailRegistrationController::class, 'showRegistrationCompletion'])->name('auth.registration-completion');
    Route::post('/auth/register/complete', [EmailRegistrationController::class, 'completeRegistration'])->name('auth.register.complete');

    // Team invitation acceptance route (accessible without login)
    Route::get('invitations/accept/{token}', [InvitationController::class, 'accept'])
        ->name('invitations.accept');

});

/*
|--------------------------------------------------------------------------
| GUEST RATE-LIMITED API-LIKE ROUTES (Throttle)
|--------------------------------------------------------------------------
| These handle phone code sending/resending/verification.
| Especially protected with rate-limiting ('throttle:5,1') to prevent abuse.
*/

Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    // Throttled AJAX/POST endpoints for phone verification

    Route::post('/auth/phone/send', [PhoneAuthController::class, 'sendCode'])->name('auth.phone.send');
    Route::post('/auth/phone/resend', [PhoneAuthController::class, 'resendCode'])->name('auth.phone.resend');
    Route::post('/auth/phone/verify', [PhoneAuthController::class, 'verifyCode'])->name('auth.phone.verify');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER DASHBOARD ROUTES (Web Auth)
|--------------------------------------------------------------------------
| Routes inside the user dashboard, require user to be logged in
| ('web', 'auth:web'). Safe for accessing internal dashboard areas.
*/

Route::middleware(['web', 'auth:web', 'require.team'])->group(function () {
    // --- DASHBOARD HOME ---
    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');

    // Team details and management
    Route::get('/dashboard/teams', [TeamController::class, 'index'])->name('dashboard.teams.index');
    Route::get('/dashboard/teams/{id}', [TeamController::class, 'show'])->name('teams.show');
    Route::get('/dashboard/teams/{id}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::put('/teams/{id}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{id}', [TeamController::class, 'destroy'])->name('teams.destroy');

    // Team members management
    Route::post('/teams/{id}/invite', [TeamController::class, 'sendInvitations'])->name('teams.invite');
    Route::put('/teams/{teamId}/members/{userId}', [TeamController::class, 'updateMemberRole'])->name('teams.members.update');
    Route::delete('/teams/{teamId}/members/{userId}', [TeamController::class, 'removeMember'])->name('teams.members.remove');

    Route::get('/dashboard/projects', [ProjectController::class, 'index'])->name('dashboard.projects');
    Route::get('/dashboard/projects/create', [ProjectController::class, 'create'])->name('dashboard.projects.create');
    Route::post('/dashboard/projects', [ProjectController::class, 'store'])->name('dashboard.projects.store');
    Route::get('/dashboard/projects/{id}', [ProjectController::class, 'show'])->name('dashboard.projects.show');
    Route::get('/dashboard/projects/{id}/edit', [ProjectController::class, 'edit'])->name('dashboard.projects.edit');
    Route::put('/dashboard/projects/{id}', [ProjectController::class, 'update'])->name('dashboard.projects.update');
    Route::delete('/dashboard/projects/{id}', [ProjectController::class, 'destroy'])->name('dashboard.projects.destroy');
});

Route::middleware(['web', 'auth:web'])->group(function () {

    // --- LOGOUT ---
    Route::post('/logout', [WebLoginController::class, 'webLogout'])->name('logout');

    Route::get('/dashboard/select-team', [DashboardController::class, 'showSelectTeam'])->name('dashboard.select-team');

    Route::post('/dashboard/select-team', [DashboardController::class, 'setCurrentTeam'])->name('dashboard.select-team');

    // Team creation
    Route::get('/dashboard/team/create', [TeamController::class, 'showCreateTeam'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');

    Route::get('invitations/accept/{token}', 'InvitationController@accept')->name('invitations.accept');
    Route::get('invitations/complete', 'InvitationController@complete')->name('invitations.complete');

    // Complete the invitation process after login/registration
    Route::get('invitations/complete', [InvitationController::class, 'complete'])
        ->name('invitations.complete');

    Route::post('invitations/accept-directly/{token}', [InvitationController::class, 'acceptDirectly'])
        ->name('invitations.accept-directly');
    Route::delete('invitations/reject/{id}', [InvitationController::class, 'reject'])
        ->name('invitations.reject');
});
