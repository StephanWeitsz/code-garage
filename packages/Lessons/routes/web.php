<?php

use Illuminate\Support\Facades\Route;
use CodeGarage\Lessons\Presentation\Http\Controllers\LessonController;

Route::prefix('courses/{courseSlug}/lessons')->group(function () {
    Route::get('/{lessonSlug}', [LessonController::class, 'show'])->name('show');
});
