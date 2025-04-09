<?php

use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


// Public routes
Route::post('login', [LoginController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('user', [LoginController::class, 'user']);
    Route::post('logout', [LoginController::class, 'logout']);
});
