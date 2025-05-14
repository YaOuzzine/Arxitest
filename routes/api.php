<?php

use App\Http\Controllers\ApiLoginController;
use App\Http\Controllers\JiraIntegrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['api', 'auth:api']);


// Public routes
Route::post('login', [ApiLoginController::class, 'apiLogin']);

// Protected routes
Route::middleware(['api', 'auth:api'])->group(function () {
    Route::get('user', [ApiLoginController::class, 'user']);
    Route::post('logout', [ApiLoginController::class, 'logout']);

    Route::get('/jira/import/progress/{progressId}', [JiraIntegrationController::class, 'checkImportProgress'])->name('api.jira.import.progress');
});

Route::get('/jira/import/progress/{progressId}', [JiraIntegrationController::class, 'getProgressJson'])
    ->name('api.jira.import.progress')
    ->middleware(['api']);

Route::get('/projects/{project}/test-cases', function (App\Models\Project $project) {
    $testCases = \App\Models\TestCase::whereHas('testSuite', function ($query) use ($project) {
        $query->where('project_id', $project->id);
    })->get(['id', 'title']);

    return response()->json([
        'success' => true,
        'test_cases' => $testCases
    ]);
});


Route::get('/projects/{project}/test-suites', function (App\Models\Project $project) {
    $testSuites = $project->testSuites()->orderBy('name')->get(['id', 'name']);

    return response()->json([
        'success' => true,
        'test_suites' => $testSuites
    ]);
});

Route::get('/projects/{project}/test-cases', function (App\Models\Project $project) {
    $testCases = App\Models\TestCase::whereHas('testSuite', function ($query) use ($project) {
        $query->where('project_id', $project->id);
    })->get(['id', 'title']);

    return response()->json([
        'success' => true,
        'test_cases' => $testCases
    ]);
});

Route::get('/projects/{project}/test-suites/{test_suite}/test-cases', function (App\Models\Project $project, App\Models\TestSuite $test_suite) {
    $testCases = $test_suite->testCases()->get(['id', 'title']);

    return response()->json([
        'success' => true,
        'test_cases' => $testCases
    ]);
});


Route::get('/projects/{project}/test-suites', function (App\Models\Project $project) {
    $storyService = app(App\Services\StoryService::class);
    $testSuites = $storyService->getProjectTestSuites($project->id);

    return response()->json([
        'success' => true,
        'test_suites' => $testSuites
    ]);
});

Route::get('/projects/{project}/test-cases', function (App\Models\Project $project) {
    $storyService = app(App\Services\StoryService::class);
    $testCases = $storyService->getProjectTestCases($project->id);

    return response()->json([
        'success' => true,
        'test_cases' => $testCases
    ]);
});

Route::get('/projects/{project}/test-suites/{test_suite}/test-cases', function (App\Models\Project $project, App\Models\TestSuite $test_suite) {
    $storyService = app(App\Services\StoryService::class);
    $testCases = $storyService->getProjectTestCases($project->id, $test_suite->id);

    return response()->json([
        'success' => true,
        'test_cases' => $testCases
    ]);
});

Route::get('/projects/{project}/stories', function (App\Models\Project $project) {
    $stories = $project
        ->stories()
        ->orderBy('title')
        ->get(['id', 'title']);

    return response()->json([
        'success' => true,
        'stories' => $stories,
    ]);
});


// Test Execution Status API
Route::get('/executions/{execution}/status', function (App\Models\TestExecution $execution) {
    return response()->json([
        'success' => true,
        'data' => [
            'status' => [
                'name' => $execution->status->name ?? 'unknown',
                'id' => $execution->status->id ?? null
            ],
            'start_time' => $execution->start_time,
            'end_time' => $execution->end_time,
            'duration' => $execution->duration
        ]
    ]);
})->middleware('api');

// Test Execution Metrics API
Route::get('/executions/{execution}/metrics', function (App\Models\TestExecution $execution) {
    $metrics = collect();

    foreach ($execution->containers as $container) {
        $containerMetrics = $container->resourceMetrics()->orderBy('metric_time')->get();
        $metrics = $metrics->concat($containerMetrics);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'metrics' => $metrics
        ]
    ]);
})->middleware('api');

// Environment Details API
Route::get('/environments/{environment}', function (App\Models\Environment $environment) {
    return response()->json([
        'success' => true,
        'data' => [
            'id' => $environment->id,
            'name' => $environment->name,
            'is_global' => $environment->is_global,
            'configuration' => $environment->configuration
        ]
    ]);
})->middleware('api');
