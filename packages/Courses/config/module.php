<?php

return [
    'route_prefix' => 'courses',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
