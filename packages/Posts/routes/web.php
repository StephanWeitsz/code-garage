<?php

use Illuminate\Support\Facades\Route;
use CodeGarage\Posts\Presentation\Http\Controllers\PostController;

Route::middleware(['auth'])->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('index');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('show');
    Route::post('/posts', [PostController::class, 'store'])->name('store');
    Route::post('/posts/{post}/replies', [PostController::class, 'reply'])->name('reply');
    Route::post('/posts/{post}/close', [PostController::class, 'close'])->name('close');
    Route::post('/posts/{post}/archive', [PostController::class, 'archive'])->name('archive');
    Route::post('/posts/{post}/reopen', [PostController::class, 'reopen'])->name('reopen');
});
