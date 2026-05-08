<?php

use CodeGarage\Events\Presentation\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/{slug}', [EventController::class, 'show'])->name('show');

    Route::middleware(['auth', 'role:admin|lecturer'])->group(function () {
        Route::post('/', [EventController::class, 'store'])->name('store');
    });
});
