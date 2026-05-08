<?php

return [
    'providers' => [
        CodeGarage\Identity\Providers\IdentityServiceProvider::class,
        CodeGarage\Courses\Providers\CoursesServiceProvider::class,
        CodeGarage\Lessons\Providers\LessonsServiceProvider::class,
        CodeGarage\Enrollments\Providers\EnrollmentsServiceProvider::class,
        CodeGarage\Payments\Providers\PaymentsServiceProvider::class,
        CodeGarage\Assignments\Providers\AssignmentsServiceProvider::class,
        CodeGarage\Posts\Providers\PostsServiceProvider::class,
        CodeGarage\Queries\Providers\QueriesServiceProvider::class,
        CodeGarage\Events\Providers\EventsServiceProvider::class,
        CodeGarage\DevelopmentRequests\Providers\DevelopmentRequestsServiceProvider::class,
    ],
];
