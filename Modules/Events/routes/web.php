<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Enums\PermissionEnum;
use Modules\Events\Http\Controllers\CategoryController;
use Modules\Events\Http\Controllers\EventsController;

Route::middleware(['auth', 'verified'])->as('events::')->group(function () {
    Route::prefix('events')->group(function () {
        Route::get('/', [EventsController::class, 'index'])->middleware('role.permission:' . PermissionEnum::VIEW_EVENT->value)
            ->name('index');
        Route::get('/create', [EventsController::class, 'create'])->middleware('role.permission:' . PermissionEnum::CREATE_EVENT->value)
            ->name('create');
        Route::get('/{event}', [EventsController::class, 'edit'])->middleware('role.permission:' . PermissionEnum::EDIT_EVENT->value)
            ->name('edit');

        Route::post('/store', [EventsController::class, 'store'])->middleware('role.permission:' . PermissionEnum::CREATE_EVENT->value)
            ->name('store');
        Route::put('/{event}', [EventsController::class, 'update'])->middleware('role.permission:' . PermissionEnum::EDIT_EVENT->value)
            ->name('update');
        Route::delete('/{event}', [EventsController::class, 'destroy'])->middleware('role.permission:' . PermissionEnum::EDIT_EVENT->value)
            ->name('destroy');
    });
    Route::prefix('categories')->as('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->middleware('role.permission:' . PermissionEnum::VIEW_EVENT->value)
            ->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->middleware('role.permission:' . PermissionEnum::CREATE_EVENT->value)
            ->name('create');
        Route::get('/{category}', [CategoryController::class, 'edit'])->middleware('role.permission:' . PermissionEnum::EDIT_EVENT->value)
            ->name('edit');

        Route::post('/store', [CategoryController::class, 'store'])->middleware('role.permission:' . PermissionEnum::CREATE_EVENT->value)
            ->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->middleware('role.permission:' . PermissionEnum::EDIT_EVENT->value)
            ->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware('role.permission:' . PermissionEnum::EDIT_EVENT->value)
            ->name('destroy');
    });
});
