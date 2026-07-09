<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\FilterController;
use Modules\Users\Http\Controllers\ProfileController;
use Modules\Users\Http\Controllers\UsersController;

Route::middleware(['auth', 'verified'])->as('users::')->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index'])
            ->middleware('role.permission:view users')
            ->name('index');
        Route::get('/create', [UsersController::class, 'create'])
            ->middleware('role.permission:create user')
            ->name('create');
        Route::post('/', [UsersController::class, 'store'])
            ->middleware('role.permission:create user')
            ->name('store');
        Route::put('/{user}/role', [UsersController::class, 'updateRole'])
            ->middleware('role.permission:edit user role')
            ->name('update-role');
        Route::delete('/{user}', [UsersController::class, 'destroy'])
            ->middleware('role.permission:edit user role')
            ->name('destroy');
    });
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });
    Route::prefix('filter')->as('filter.')->group(function () {
        Route::get('/edit', [FilterController::class, 'edit'])->name('edit');
        Route::put('/', [FilterController::class, 'update'])->name('update');
    });
});
