<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::patch('profile', [\Modules\Users\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::patch('filter', [\Modules\Users\Http\Controllers\Api\FilterController::class, 'update']);
    Route::post('feedback', [\Modules\Users\Http\Controllers\Api\FeedbackController::class, 'store']);
});
