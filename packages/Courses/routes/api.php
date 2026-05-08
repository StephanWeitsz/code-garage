<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'module' => 'Courses',
        'status' => 'ok',
    ]);
})->name('health');
