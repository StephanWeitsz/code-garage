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

    'block_invalid_hosts' => env('ANALYTICS_BLOCK_INVALID_HOSTS', true),

    'allowed_hosts' => array_values(array_filter(array_map('trim', explode(',', env(
        'ANALYTICS_ALLOWED_HOSTS',
        'code-garage.co.za,www.code-garage.co.za'
    ))))),

    'high_risk_query_keys' => [
        'XDEBUG_SESSION_START',
        'xdebug_session_start',
        'phpinfo',
    ],

    'high_risk_patterns' => [
        '.env',
        '.git',
        'phpinfo',
        'xdebug_session_start',
        'wp-admin',
        'wp-login',
        'xmlrpc.php',
        'phpunit',
        'vendor/phpunit',
        'eval-stdin.php',
        'composer.json',
        'composer.lock',
        'config.php',
        'database.sql',
        'backup',
        'shell.php',
        'cmd.php',
    ],

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
