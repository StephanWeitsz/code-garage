<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['module' => 'events', 'ok' => true])->name('health');
