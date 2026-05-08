<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'module' => 'Assignments',
        'status' => 'ok',
    ]);
})->name('health');
