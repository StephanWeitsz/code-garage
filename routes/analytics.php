<?php

use App\Http\Controllers\AnalyticsCourseEventController;
use App\Livewire\Admin\AnalyticsDashboard;
use App\Livewire\Admin\VisitorActivity;
use Illuminate\Support\Facades\Route;

Route::middleware(config('analytics.admin_middleware', ['web', 'auth']))
    ->prefix(config('analytics.admin_prefix', 'admin/analytics'))
    ->name('admin.analytics.')
    ->group(function (): void {
        Route::get('/', AnalyticsDashboard::class)->name('dashboard');
        Route::get('/visitors/{visitorSession}', VisitorActivity::class)->name('visitors.show');
    });

Route::middleware('web')
    ->post('/analytics/course-event', AnalyticsCourseEventController::class)
    ->name('analytics.course-event');
