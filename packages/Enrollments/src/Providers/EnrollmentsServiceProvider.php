<?php

namespace CodeGarage\Enrollments\Providers;

use CodeGarage\Shared\Providers\BaseModuleServiceProvider;

class EnrollmentsServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Enrollments';

    protected string $moduleNameLower = 'enrollments';

    protected array $moduleBindings = [
        \CodeGarage\Enrollments\Domain\Repositories\EnrollmentRepository::class => \CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Repositories\EloquentEnrollmentRepository::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom($this->modulePath('resources/views'), 'enrollments');
    }
}
