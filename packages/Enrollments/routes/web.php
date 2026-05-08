<?php

use Illuminate\Support\Facades\Route;
use CodeGarage\Enrollments\Presentation\Http\Controllers\EnrollmentController;
use CodeGarage\Enrollments\Presentation\Http\Controllers\ProgressController;

Route::middleware(['auth'])->group(function () {
    Route::get('/my-learning', [EnrollmentController::class, 'index'])->name('index');
    Route::get('/progress', ProgressController::class)->name('progress');
    Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('store');
    Route::post('/enrollments/{enrollmentId}/meeting-link', [EnrollmentController::class, 'updateMeetingLink'])->name('meeting-link');
    Route::post('/lessons/{lessonId}/complete', [EnrollmentController::class, 'completeLesson'])->name('complete-lesson');
});
