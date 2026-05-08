<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'module' => 'Posts',
        'status' => 'ok',
    ]);
})->name('health');
