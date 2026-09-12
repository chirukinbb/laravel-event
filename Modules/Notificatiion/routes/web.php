<?php

use Illuminate\Support\Facades\Route;
use Modules\Notificatiion\Http\Controllers\NotificatiionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('notificatiions', NotificatiionController::class)->names('notificatiion');
});
