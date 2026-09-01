<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\Api\CategoryController;
use Modules\Events\Http\Controllers\Api\EventsController;
use Modules\Events\Http\Controllers\Api\MemberController;
use Modules\Events\Http\Middlewares\EventOwnerMiddleware;
use Modules\Events\Http\Middlewares\MemberMiddleware;
use Modules\Events\Http\Middlewares\ReservableMiddleware;
use Modules\Events\Middlewares\HasFilterMiddleware;
use Modules\Events\Middlewares\TimeMiddleware;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::middleware(HasFilterMiddleware::class)->prefix('events')->group(function () {
        Route::get('', [EventsController::class, 'index']);
        Route::get('organizing', [EventsController::class, 'organizing']);
        Route::get('attending', [EventsController::class, 'attending']);
    });

    Route::prefix('event/{event}')->group(function () {
        Route::middleware(EventOwnerMiddleware::class)->group(function () {
            Route::put('/', [EventsController::class, 'update']);
            Route::delete('/', [EventsController::class, 'destroy']);
        });

        Route::prefix('member/{member}')->middleware(['auth:sanctum', MemberMiddleware::class])->group(function () {
            Route::patch('', [MemberController::class, 'update']);
            Route::delete('unsubscribe', [MemberController::class, 'destroy'])->middleware(EventOwnerMiddleware::class);
        });

        Route::post('subscribe', [MemberController::class, 'create'])->middleware(ReservableMiddleware::class);
        Route::delete('unsubscribe', [MemberController::class, 'destroy'])->middleware(TimeMiddleware::class);
    });

    Route::post('events', [EventsController::class, 'store']);
    Route::get('event/{event}', [EventsController::class, 'show']);
});

Route::get('v1/categories', [CategoryController::class, 'index']);
Route::get('v1/tags', [CategoryController::class, 'tags']);
