<?php

use Illuminate\Support\Facades\Route;
use Modules\Bot\Http\Controllers\BotController;

Route::prefix('v1')->group(function () {
    Route::post('bot/login', [BotController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('bot/events', [BotController::class, 'createEvent']);
        Route::post('bot/events/flow', [BotController::class, 'flow']);
        Route::post('bot/chat/{chat}/messages', [BotController::class, 'sendMessage']);
        Route::post('bot/events/{event}/subscribe', [BotController::class, 'subscribe']);
        Route::delete('bot/events/{event}/unsubscribe', [BotController::class, 'unsubscribe']);
        Route::patch('bot/events/{event}/members/{member}/comment', [BotController::class, 'comment']);
    });
});
