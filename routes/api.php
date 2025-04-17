<?php

use App\Http\Controllers\ApiLoginController;
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
});

