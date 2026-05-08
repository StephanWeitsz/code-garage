<?php

namespace CodeGarage\Courses\Providers;

use CodeGarage\Shared\Providers\BaseModuleServiceProvider;

class CoursesServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Courses';

    protected string $moduleNameLower = 'courses';

    protected array $moduleBindings = [
        \CodeGarage\Courses\Domain\Repositories\CourseRepository::class => \CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Repositories\EloquentCourseRepository::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom($this->modulePath('resources/views'), 'courses');
    }
}
