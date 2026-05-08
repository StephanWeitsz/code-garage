<?php

namespace CodeGarage\Posts\Providers;

use CodeGarage\Shared\Providers\BaseModuleServiceProvider;

class PostsServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Posts';

    protected string $moduleNameLower = 'posts';

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom($this->modulePath('resources/views'), 'posts');
    }
}
