<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->as('api.auth.')->group(function () {
    Route::get('/{provider}/redirect', [AuthController::class, 'index'])->name('redirect');
    Route::get('/{provider}/callback', [AuthController::class, 'entry'])->name('callback');
});
