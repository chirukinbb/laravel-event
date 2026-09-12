<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\Http\Controllers\Api\ChatsController;
use Modules\Chat\Http\Middleware\EventMemberMiddleware;
use Modules\Chat\Http\Middlewares\MessageAuthorMiddleware;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('chat/{chat}')->group(function () {
        Route::middleware(EventMemberMiddleware::class)->group(function () {
            Route::get('/', [ChatsController::class, 'index']);
            Route::post('/', [ChatsController::class, 'store']);
        });
        Route::middleware(MessageAuthorMiddleware::class)->prefix('message/{message}')->group(function () {
            Route::get('/', [ChatsController::class, 'show']);
            Route::patch('/', [ChatsController::class, 'update']);
            Route::delete('/', [ChatsController::class, 'destroy']);
        });
    });
});
