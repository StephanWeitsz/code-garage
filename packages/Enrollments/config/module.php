<?php

return [
    'api_prefix' => 'api',
    'route_prefix' => 'enrollments',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
