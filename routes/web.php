<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordController;

// Redirect root URL to the appropriate page based on authentication status
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('password.change');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [PasswordController::class, 'showLoginForm'])->name('login');
Route::post('/login', [PasswordController::class, 'login']);
Route::post('/logout', [PasswordController::class, 'logout'])->name('logout');

// Password management routes
Route::prefix('password')->group(function () {
    // Authenticated users can change their password
    Route::middleware('custom.auth')->group(function () {
        Route::get('/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
        Route::post('/change', [PasswordController::class, 'changePassword']);
    });

    // Guests can recover and reset their password
    Route::middleware('custom.guest')->group(function () {
        Route::get('/recover', [PasswordController::class, 'showRecoveryForm'])->name('password.request');
        Route::post('/recover', [PasswordController::class, 'requestRecovery']);
        Route::get('/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset', [PasswordController::class, 'resetPassword']);
    });
});
