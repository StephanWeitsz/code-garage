<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeploymentToolsController;
use App\Http\Controllers\LecturerProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('welcome');
Route::get('/lecturers/{lecturer}', LecturerProfileController::class)->name('lecturers.show');

Route::middleware(['auth:sanctum'])->get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware(['auth', 'role:admin', 'deployment.tools'])->group(function () {
    Route::get('/deployment-tools', [DeploymentToolsController::class, 'index'])->name('deployment-tools.index');
    Route::post('/deployment-tools/run', [DeploymentToolsController::class, 'run'])->name('deployment-tools.run');
});

require __DIR__.'/analytics.php';