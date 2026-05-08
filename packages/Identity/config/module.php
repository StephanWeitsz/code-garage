<?php

return [
    'route_prefix' => 'identity',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
