<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InboxController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
Route::get('/inbox/search', [InboxController::class, 'search'])->name('inbox.search');
