<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'module' => 'Payments',
        'status' => 'ok',
    ]);
})->name('health');
