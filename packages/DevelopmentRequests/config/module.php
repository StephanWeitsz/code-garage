<?php

return [
    'route_prefix' => 'development-requests',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
