<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailRegistrationController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\JiraImportController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PhoneAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TestCaseController;
use App\Http\Controllers\TestDataController;
use App\Http\Controllers\TestExecutionController;
use App\Http\Controllers\TestScriptController;
use App\Http\Controllers\TestSuiteController;
use App\Http\Controllers\WebLoginController;
use Illuminate\Support\Facades\Route;
use Twilio\Rest\Voice\V1\DialingPermissions\SettingsContext;

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
    Route::get('/register', function () {
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
    Route::get('/dashboard/projects/{project}', [ProjectController::class, 'show'])->name('dashboard.projects.show');
    Route::get('/dashboard/projects/{project}/edit', [ProjectController::class, 'edit'])->name('dashboard.projects.edit');
    Route::put('/dashboard/projects/{project}', [ProjectController::class, 'update'])->name('dashboard.projects.update');
    Route::delete('/dashboard/projects/{project}', [ProjectController::class, 'destroy'])->name('dashboard.projects.destroy');

    Route::get('/dashboard/test-suites', [TestSuiteController::class, 'indexAll'])->name('dashboard.test-suites.indexAll');

    // Routes nested under projects
    Route::prefix('/dashboard/projects/{project}/test-suites')->name('dashboard.projects.test-suites.')->group(function () {
        Route::get('/', [TestSuiteController::class, 'index'])->name('index'); // List suites for a specific project
        Route::get('/create', [TestSuiteController::class, 'create'])->name('create'); // Show create form
        Route::post('/', [TestSuiteController::class, 'store'])->name('store'); // Store new suite
        Route::get('/{test_suite}', [TestSuiteController::class, 'show'])->name('show'); // Show specific suite
        Route::get('/{test_suite}/edit', [TestSuiteController::class, 'edit'])->name('edit'); // Show edit form
        Route::put('/{test_suite}', [TestSuiteController::class, 'update'])->name('update'); // Update specific suite
        Route::delete('/{test_suite}', [TestSuiteController::class, 'destroy'])->name('destroy'); // Delete specific suite

        // AI Generation Route (AJAX)
        Route::post('/generate-ai', [TestSuiteController::class, 'generateWithAI'])->name('generateAI');
    });

    // Global Test Cases Index (All projects/suites)
    Route::get('/dashboard/test-cases', [TestCaseController::class, 'indexAll'])
        ->name('dashboard.test-cases.indexAll');

    // Project-specific Test Cases
    Route::prefix('/dashboard/projects/{project}/test-cases')->name('dashboard.projects.test-cases.')->group(function () {
        Route::get('/', [TestCaseController::class, 'index'])->name('index');
        Route::get('/create', [TestCaseController::class, 'create'])->name('create');
        Route::post('/', [TestCaseController::class, 'store'])->name('store');
        Route::get('/{test_case}', [TestCaseController::class, 'show'])->name('show');
        Route::get('/{test_case}/edit', [TestCaseController::class, 'edit'])->name('edit');
        Route::put('/{test_case}', [TestCaseController::class, 'update'])->name('update');
        Route::delete('/{test_case}', [TestCaseController::class, 'destroy'])->name('destroy');
    });

    // Test Suite-specific Test Cases
    Route::prefix('/dashboard/projects/{project}/test-suites/{test_suite}/test-cases')->name('dashboard.projects.test-suites.test-cases.')->group(function () {
        Route::get('/', [TestCaseController::class, 'indexBySuite'])->name('index');
        Route::get('/create', [TestCaseController::class, 'createForSuite'])->name('create');
        Route::post('/', [TestCaseController::class, 'storeForSuite'])->name('store');
        Route::get('/{test_case}', [TestCaseController::class, 'showForSuite'])->name('show');
        Route::get('/{test_case}/edit', [TestCaseController::class, 'editForSuite'])->name('edit');
        Route::put('/{test_case}', [TestCaseController::class, 'updateForSuite'])->name('update');
        Route::delete('/{test_case}', [TestCaseController::class, 'destroyForSuite'])->name('destroy');
    });

    // Test Scripts Routes
    Route::prefix('/dashboard/projects/{project}/test-cases/{test_case}/scripts')->name('dashboard.projects.test-cases.scripts.')->group(function () {
        Route::get('/', [TestScriptController::class, 'index'])->name('index');
        Route::post('/', [TestScriptController::class, 'store'])->name('store');
        Route::get('/{test_script}', [TestScriptController::class, 'show'])->name('show');
        Route::delete('/{test_script}', [TestScriptController::class, 'destroy'])->name('destroy');
        Route::post('/generate-ai', [TestScriptController::class, 'generateWithAI'])->name('generateAI');
    });

    // Test Data Routes
    Route::prefix('/dashboard/projects/{project}/test-cases/{test_case}/data')->name('dashboard.projects.test-cases.data.')->group(function () {
        Route::get('/', [TestDataController::class, 'index'])->name('index');
        Route::post('/', [TestDataController::class, 'store'])->name('store');
        Route::get('/{test_data}', [TestDataController::class, 'show'])->name('show');
        Route::delete('/{test_data}', [TestDataController::class, 'detach'])->name('detach');
        Route::post('/generate-ai', [TestDataController::class, 'generateWithAI'])->name('generateAI');
    });

    // Global Stories Index (All projects)
    Route::get('/dashboard/stories', [StoryController::class, 'indexAll'])
        ->name('dashboard.stories.indexAll');

    // Story CRUD routes
    Route::prefix('/dashboard/stories')->name('dashboard.stories.')->group(function () {
        Route::get('/create', [StoryController::class, 'create'])->name('create');
        Route::post('/', [StoryController::class, 'store'])->name('store');
        Route::get('/{story}', [StoryController::class, 'show'])->name('show');
        Route::get('/{story}/edit', [StoryController::class, 'edit'])->name('edit');
        Route::put('/{story}', [StoryController::class, 'update'])->name('update');
        Route::delete('/{story}', [StoryController::class, 'destroy'])->name('destroy');
    });

    // Project-specific Stories
    Route::prefix('/dashboard/projects/{project}/stories')->name('dashboard.projects.stories.')->group(function () {
        Route::get('/', [StoryController::class, 'index'])->name('index');
    });

    Route::get('/dashboard/integrations', [IntegrationController::class, 'index'])
        ->name('dashboard.integrations.index');
    Route::get('/integrations/jira/redirect', [IntegrationController::class, 'jiraRedirect'])
        ->name('integrations.jira.redirect');
    Route::post('/integrations/jira/disconnect', [IntegrationController::class, 'jiraDisconnect'])
        ->name('integrations.jira.disconnect');
    Route::prefix('dashboard/integrations/jira')->name('integrations.jira.')->group(function () {
        // Connection routes
        Route::get('/redirect', [IntegrationController::class, 'jiraRedirect'])
            ->name('redirect');
        Route::get('/callback', [IntegrationController::class, 'jiraCallback'])
            ->name('callback');
        Route::post('/disconnect', [IntegrationController::class, 'jiraDisconnect'])
            ->name('disconnect');

        // Import routes
        Route::get('/import-options', [IntegrationController::class, 'showJiraImportOptions'])
            ->name('import.options');
        Route::get('/import-new', [IntegrationController::class, 'showImportNewOptions'])
            ->name('import.new');
        Route::get('/import-to-existing', [IntegrationController::class, 'showImportToExistingOptions'])
            ->name('import.existing');

        // Metadata and preview endpoints
        Route::post('/project-metadata', [IntegrationController::class, 'getJiraProjectMetadata'])
            ->name('project-metadata');
        Route::post('/preview-import', [IntegrationController::class, 'previewJiraImport'])
            ->name('preview-import');

        // Import execution and progress tracking
        Route::post('/import', [JiraImportController::class, 'importProject'])
            ->name('import.project');
        Route::get('/import/progress/{project_id?}', [JiraImportController::class, 'getImportProgress'])
            ->name('import.progress');
    });
});

Route::middleware(['web'])->group(function () {
    // OAuth callback - NO auth required
    Route::get('/jira/callback', [IntegrationController::class, 'jiraCallback'])
        ->name('integrations.jira.callback');
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
    Route::get('invitations/complete', [InvitationController::class, 'complete'])->name('invitations.complete');

    // Complete the invitation process after login/registration
    Route::get('invitations/complete', [InvitationController::class, 'complete'])
        ->name('invitations.complete');

    Route::post('invitations/accept-directly/{token}', [InvitationController::class, 'acceptDirectly'])
        ->name('invitations.accept-directly');
    Route::delete('invitations/reject/{id}', [InvitationController::class, 'reject'])
        ->name('invitations.reject');

    Route::prefix('dashboard/profile')->name('dashboard.profile.')->middleware(['web', 'auth:web'])->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::get('/password', [ProfileController::class, 'editPassword'])->name('password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/security', [ProfileController::class, 'security'])->name('security');
        Route::get('/connections', [ProfileController::class, 'connections'])->name('connections');
        Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
    });
    // Profile notification settings update route
    Route::put('/dashboard/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('dashboard.profile.notifications.update');
    Route::get('/dashboard/settings', [SettingsController::class, 'index'])->name('dashboard.settings.index');

    // OAuth disconnect route (for connected accounts)
    Route::post('/oauth/disconnect/{provider}', [OAuthController::class, 'disconnect'])->name('oauth.disconnect');
});

// Settings routes
Route::prefix('dashboard/settings')->name('dashboard.settings.')->middleware(['web', 'auth:web'])->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::post('/app', [SettingsController::class, 'updateAppSettings'])->name('app');
    Route::post('/test-execution', [SettingsController::class, 'updateTestExecutionSettings'])->name('test-execution');
});

// Test Execution Routes
Route::prefix('/dashboard/executions')->name('dashboard.executions.')->middleware(['web', 'auth:web', 'require.team'])->group(function () {
    Route::get('/', [TestExecutionController::class, 'index'])->name('index');
    Route::get('/create', [TestExecutionController::class, 'create'])->name('create');
    Route::post('/', [TestExecutionController::class, 'store'])->name('store');
    Route::get('/{execution}', [TestExecutionController::class, 'show'])->name('show');
    Route::post('/{execution}/abort', [TestExecutionController::class, 'abort'])->name('abort');
});

Route::get('/emergency-stop/{id}', [TestExecutionController::class, 'emergencyStop'])
    ->name('dashboard.executions.emergency-stop');
Route::get('/dashboard/executions/{id}/logs', [TestExecutionController::class, 'loadMoreLogs'])
->name('dashboard.executions.logs');
Route::get('/dashboard/executions/{execution}/logs/download', [TestExecutionController::class, 'downloadLogs'])
    ->name('dashboard.executions.logs.download');

Route::prefix('/dashboard/environments')->name('dashboard.environments.')->middleware(['web', 'auth:web', 'require.team'])->group(function () {
    Route::get('/', [EnvironmentController::class, 'index'])->name('index');
    Route::get('/create', [EnvironmentController::class, 'create'])->name('create');
    Route::post('/', [EnvironmentController::class, 'store'])->name('store');
    Route::get('/{environment}', [EnvironmentController::class, 'show'])->name('show');
    Route::get('/{environment}/edit', [EnvironmentController::class, 'edit'])->name('edit');
    Route::put('/{environment}', [EnvironmentController::class, 'update'])->name('update');
    Route::delete('/{environment}', [EnvironmentController::class, 'destroy'])->name('destroy');
});
