<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestScriptController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/auth/debug-token', [AuthController::class, 'debugTokenCreation']);
// Public API routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/validate-field', [AuthController::class, 'validateField']);
// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/test-scripts', [TestScriptController::class, 'index']);

    // Add other protected API routes here...
});
