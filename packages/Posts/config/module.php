<?php

return [
    'route_prefix' => 'posts',
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
    ],
];
