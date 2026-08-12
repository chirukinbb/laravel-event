<?php

use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get("/login", [AuthController::class, "showLoginForm"])->name("login");
Route::post("/login", [AuthController::class, "login"])->name('signin');
Route::get("/register", function () {
    return view('auth.register');
})->name('register.form');
Route::post("/register", [AuthController::class, "register"])->name('register');
Route::post("/logout", [AuthController::class, "logout"])->name("logout");

// Password Reset Routes
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Password Confirmation Routes
Route::get('/password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
Route::post('/password/confirm', [ConfirmPasswordController::class, 'confirm']);

// Email Verification Routes
Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// Simple dashboard (protected)
Route::get("/dashboard", [\App\Http\Controllers\DashboardController::class, 'index'])->middleware('auth')->name("dashboard");

// Redirect root to login
Route::get("/", function () {
    return redirect()->route('login');
});

// User management routes (protected)
Route::middleware(['auth'])->group(function () {


    // Settings routes
    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('role.permission:view settings')
        ->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])
        ->middleware('role.permission:view settings')
        ->name('settings.update');


});

Route::prefix('auth')->as('auth.')->group(function () {
    Route::get('/{provider}/redirect', [AuthController::class, 'socialLogin'])->name('redirect');
    Route::get('/{provider}/callback', [AuthController::class, 'socialEntry'])->name('callback');
});

