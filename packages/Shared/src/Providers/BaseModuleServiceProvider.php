<?php

namespace CodeGarage\Shared\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

abstract class BaseModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = '';

    protected string $moduleNameLower = '';

    /**
     * @var array<class-string, class-string|array<int, class-string>>
     */
    protected array $listen = [];

    /**
     * @var array<class-string, class-string>
     */
    protected array $moduleBindings = [];

    /**
     * @var array<class-string, class-string>
     */
    protected array $moduleSingletons = [];

    public function register(): void
    {
        $this->registerConfig();
        $this->registerBindings();
    }

    public function boot(): void
    {
        $this->bootConfig();
        $this->bootRoutes();
        $this->bootMigrations();
        $this->registerEventListeners();
    }

    protected function registerConfig(): void
    {
        $configPath = $this->modulePath('config/module.php');

        if (is_file($configPath)) {
            $this->mergeConfigFrom($configPath, sprintf('modules.%s', $this->moduleNameLower));
        }
    }

    protected function bootConfig(): void
    {
        $configPath = $this->modulePath('config/module.php');

        if (is_file($configPath)) {
            $this->publishes([
                $configPath => config_path(sprintf('%s.php', $this->moduleNameLower)),
            ], sprintf('%s-config', $this->moduleNameLower));
        }
    }

    protected function bootRoutes(): void
    {
        $apiRoutes = $this->modulePath('routes/api.php');
        $webRoutes = $this->modulePath('routes/web.php');

        if (is_file($apiRoutes)) {
            $apiPrefix = trim((string) config(sprintf('modules.%s.api_prefix', $this->moduleNameLower), 'api'), '/');
            $routePrefix = trim((string) config(sprintf('modules.%s.route_prefix', $this->moduleNameLower), $this->moduleNameLower), '/');
            $prefix = trim($apiPrefix.'/'.$routePrefix, '/');

            Route::middleware(config(sprintf('modules.%s.middleware.api', $this->moduleNameLower), ['api']))
                ->prefix($prefix)
                ->as(sprintf('%s.', $this->moduleNameLower))
                ->group($apiRoutes);
        }

        if (is_file($webRoutes)) {
            Route::middleware(config(sprintf('modules.%s.middleware.web', $this->moduleNameLower), ['web']))
                ->as(sprintf('%s.', $this->moduleNameLower))
                ->group($webRoutes);
        }
    }

    protected function bootMigrations(): void
    {
        $migrationPath = $this->modulePath('database/migrations');

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function registerBindings(): void
    {
        foreach ($this->moduleBindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }

        foreach ($this->moduleSingletons as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }

    protected function registerEventListeners(): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ((array) $listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    protected function modulePath(string $path = ''): string
    {
        $reflection = new ReflectionClass($this);
        $basePath = dirname($reflection->getFileName(), 3);

        return $path === '' ? $basePath : $basePath.DIRECTORY_SEPARATOR.$path;
    }
}
