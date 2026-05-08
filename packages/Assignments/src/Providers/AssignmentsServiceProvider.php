<?php

namespace CodeGarage\Assignments\Providers;

use CodeGarage\Shared\Providers\BaseModuleServiceProvider;

class AssignmentsServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Assignments';

    protected string $moduleNameLower = 'assignments';

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom($this->modulePath('resources/views'), 'assignments');
    }
}
