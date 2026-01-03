<?php

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Health check endpoint
Route::get('/health', HealthCheckResultsController::class);

// Routes d'authentification
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});
