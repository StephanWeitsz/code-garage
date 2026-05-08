<?php

namespace CodeGarage\Events\Providers;

use CodeGarage\Shared\Providers\BaseModuleServiceProvider;

class EventsServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Events';

    protected string $moduleNameLower = 'events';

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom($this->modulePath('resources/views'), 'events');
    }
}
