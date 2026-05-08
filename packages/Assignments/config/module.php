<?php

return [
    'route_prefix' => 'assignments',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
