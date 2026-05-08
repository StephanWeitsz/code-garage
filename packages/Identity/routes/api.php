<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'module' => 'Identity',
        'status' => 'ok',
    ]);
})->name('health');
