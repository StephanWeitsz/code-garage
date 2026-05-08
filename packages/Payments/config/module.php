<?php

return [
    'route_prefix' => 'payments',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
