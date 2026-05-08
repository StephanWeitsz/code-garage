<?php

return [
    'api_prefix' => 'api',
    'route_prefix' => 'lessons',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
