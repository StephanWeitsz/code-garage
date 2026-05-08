<?php

use Illuminate\Support\Facades\Route;
use CodeGarage\Assignments\Presentation\Http\Controllers\AssignmentController;

Route::middleware(['auth'])->group(function () {
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('show');

    Route::post('/assignments', [AssignmentController::class, 'store'])
        ->middleware('role:admin|lecturer')
        ->name('store');

    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])
        ->middleware('role:student')
        ->name('submit');

    Route::post('/assignments/{assignment}/submissions/{submission}/grade', [AssignmentController::class, 'grade'])
        ->middleware('role:admin|lecturer')
        ->name('grade');
});
