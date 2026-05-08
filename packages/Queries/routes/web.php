<?php

use CodeGarage\Queries\Presentation\Http\Controllers\CourseQueryController;
use Illuminate\Support\Facades\Route;

Route::post('/courses/{course}/queries', [CourseQueryController::class, 'store'])
    ->name('course-queries.store');
