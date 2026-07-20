<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Enums\PermissionEnum;
use Modules\Events\Http\Controllers\Api\EventsController;
use Modules\Events\Http\Controllers\Api\MemberController;
use Modules\Events\Http\Middlewares\MemberMiddleware;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('events', [EventsController::class, 'index'])->middleware('role.permission:' . PermissionEnum::API_VIEW_EVENT_LIST->value);

    Route::prefix('event/{event}')->middleware(\Modules\Events\Http\Middlewares\EventOwnerMiddleware::class)->group(function () {
        Route::put('/', [EventsController::class, 'update'])->middleware('role.permission:' . PermissionEnum::API_EDIT_EVENT->value);
        Route::delete('/', [EventsController::class, 'destroy'])->middleware('role.permission:' . PermissionEnum::API_CREATE_EVENT->value);

        Route::post('subscribe', [MemberController::class, 'create'])->middleware(['auth:sanctum', \Modules\Events\Http\Middlewares\ReservableMiddleware::class]);

        Route::prefix('member/{member}')->middleware(['auth:sanctum', MemberMiddleware::class])->group(function () {
            Route::patch('', [MemberController::class, 'update']);
            Route::delete('unsubscribe', [MemberController::class, 'destroy']);
        });
    });

    Route::post('events', [EventsController::class, 'store'])->middleware('role.permission:' . PermissionEnum::API_CREATE_EVENT->value);
    Route::get('event/{event}', [EventsController::class, 'show'])->middleware('role.permission:' . PermissionEnum::API_VIEW_EVENT->value);

    Route::get('categories', [\Modules\Events\Http\Controllers\Api\CategoryController::class, 'index']);
});
