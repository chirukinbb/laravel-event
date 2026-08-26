<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\Api\EventsController;
use Modules\Events\Http\Controllers\Api\MemberController;
use Modules\Events\Http\Middlewares\MemberMiddleware;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::middleware(\Modules\Events\Middlewares\HasFilterMiddleware::class)->prefix('events')->group(function () {
        Route::get('', [EventsController::class, 'index']);
        Route::get('organizing', [EventsController::class, 'organizing']);
        Route::get('attending', [EventsController::class, 'attending']);
    });

    Route::prefix('event/{event}')->middleware(\Modules\Events\Http\Middlewares\EventOwnerMiddleware::class)->group(function () {
        Route::middleware(\Modules\Events\Http\Middlewares\EventOwnerMiddleware::class)->group(function () {
            Route::put('/', [EventsController::class, 'update']);
            Route::delete('/', [EventsController::class, 'destroy']);

            Route::prefix('member/{member}')->middleware(['auth:sanctum', MemberMiddleware::class])->group(function () {
                Route::patch('', [MemberController::class, 'update']);
                Route::delete('unsubscribe', [MemberController::class, 'destroy']);
            });
        });

        Route::post('subscribe', [MemberController::class, 'create'])->middleware([\Modules\Events\Http\Middlewares\ReservableMiddleware::class]);
        Route::post('unsubscribe', [MemberController::class, 'destroy'])->middleware([\Modules\Events\Http\Middlewares\ReservableMiddleware::class]);
    });

    Route::post('events', [EventsController::class, 'store']);
    Route::get('event/{event}', [EventsController::class, 'show']);
});

Route::get('v1/categories', [\Modules\Events\Http\Controllers\Api\CategoryController::class, 'index']);
Route::get('v1/tags', [\Modules\Events\Http\Controllers\Api\CategoryController::class, 'tags']);
