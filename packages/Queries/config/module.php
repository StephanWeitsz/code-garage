<?php

return [
    'route_prefix' => 'queries',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
