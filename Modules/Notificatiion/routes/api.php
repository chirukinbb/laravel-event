<?php

use Illuminate\Support\Facades\Route;
use Modules\Notificatiion\Http\Controllers\NotificatiionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('notificatiions', NotificatiionController::class)->names('notificatiion');
});
