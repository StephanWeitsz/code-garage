<?php

use CodeGarage\DevelopmentRequests\Presentation\Http\Controllers\DevelopmentRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/services', [DevelopmentRequestController::class, 'index'])->name('services.index');
Route::get('/services/requirements', [DevelopmentRequestController::class, 'create'])->name('services.requirements.create');
Route::post('/services/requirements', [DevelopmentRequestController::class, 'store'])->name('services.requirements.store');
Route::get('/services/requirements/thank-you', [DevelopmentRequestController::class, 'thankYou'])->name('services.requirements.thank-you');
