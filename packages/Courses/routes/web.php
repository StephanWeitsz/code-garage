<?php

use Illuminate\Support\Facades\Route;
use CodeGarage\Courses\Presentation\Http\Controllers\CourseCatalogController;
use CodeGarage\Courses\Presentation\Http\Controllers\ManageCourseController;

Route::prefix('courses')->group(function () {
    Route::get('/', [CourseCatalogController::class, 'index'])->name('index');
    Route::get('/{slug}', [CourseCatalogController::class, 'show'])->name('show');

    Route::middleware(['auth', 'role:admin|lecturer'])->group(function () {
        Route::post('/', [ManageCourseController::class, 'store'])->name('store');
        Route::put('/{course}', [ManageCourseController::class, 'update'])->name('update');
    });
});
