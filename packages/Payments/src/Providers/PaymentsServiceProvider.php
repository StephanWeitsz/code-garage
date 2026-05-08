<?php

namespace CodeGarage\Payments\Providers;

use CodeGarage\Shared\Providers\BaseModuleServiceProvider;

class PaymentsServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Payments';

    protected string $moduleNameLower = 'payments';

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom($this->modulePath('resources/views'), 'payments');
    }
}
