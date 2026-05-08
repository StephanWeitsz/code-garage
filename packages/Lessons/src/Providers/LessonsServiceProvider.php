<?php

namespace CodeGarage\Lessons\Providers;

use CodeGarage\Shared\Providers\BaseModuleServiceProvider;

class LessonsServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Lessons';

    protected string $moduleNameLower = 'lessons';

    protected array $moduleBindings = [
        \CodeGarage\Lessons\Domain\Repositories\CourseSectionRepository::class => \CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Repositories\EloquentCourseSectionRepository::class,
        \CodeGarage\Lessons\Domain\Repositories\LessonRepository::class => \CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Repositories\EloquentLessonRepository::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom($this->modulePath('resources/views'), 'lessons');
    }
}
