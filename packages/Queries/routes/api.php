<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'module' => 'Queries',
        'status' => 'ok',
    ]);
})->name('health');
