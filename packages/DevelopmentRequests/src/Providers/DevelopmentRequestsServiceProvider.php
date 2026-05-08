<?php

namespace CodeGarage\DevelopmentRequests\Providers;

use CodeGarage\Shared\Providers\BaseModuleServiceProvider;

class DevelopmentRequestsServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'DevelopmentRequests';

    protected string $moduleNameLower = 'development-requests';

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom($this->modulePath('resources/views'), 'development-requests');
    }
}
