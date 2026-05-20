<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Visitor Analytics
    |--------------------------------------------------------------------------
    |
    | Lightweight first-party analytics settings. Keep this list conservative:
    | analytics should never capture auth, password, token, Livewire transport,
    | or asset requests.
    |
    */

    'enabled' => env('ANALYTICS_ENABLED', true),

    'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 180),

    'active_window_minutes' => (int) env('ANALYTICS_ACTIVE_WINDOW_MINUTES', 5),

    'cache_ttl_seconds' => (int) env('ANALYTICS_CACHE_TTL_SECONDS', 60),

    'admin_middleware' => ['web', 'auth'],

    'admin_prefix' => env('ANALYTICS_ADMIN_PREFIX', 'admin/analytics'),

    'ignored_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],

    'ignored_paths' => [
        'login',
        'logout',
        'register',
        'forgot-password',
        'reset-password*',
        'email/verify*',
        'confirm-password',
        'two-factor-challenge',
        'livewire/*',
        'admin/login',
        'admin/logout',
        'admin/password*',
        'admin/auth*',
        'storage/*',
        'build/*',
        'css/*',
        'js/*',
        'images/*',
        'img/*',
        'vendor/*',
        'favicon.ico',
        'robots.txt',
    ],

    'ignored_route_names' => [
        'login',
        'logout',
        'password.*',
        'verification.*',
        'livewire.*',
        'filament.*.auth.*',
    ],

    'course_route_patterns' => [
        'courses.show',
        'course.show',
        'courses.*.show',
    ],

    'course_url_patterns' => [
        'courses/*',
        'course/*',
    ],
];
