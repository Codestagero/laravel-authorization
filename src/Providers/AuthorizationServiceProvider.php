<?php

namespace Codestage\Authorization\Providers;

use Codestage\Authorization\Console\Commands\InstallCommand;
use Codestage\Authorization\Contracts\Services\IAuthorizationService;
use Codestage\Authorization\Contracts\Services\IPolicyService;
use Codestage\Authorization\Services\AuthorizationService;
use Codestage\Authorization\Services\PolicyService;
use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * All the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        IAuthorizationService::class => AuthorizationService::class,
        IPolicyService::class => PolicyService::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../config/authorization.php' => $this->app->configPath('authorization.php'),
        ], 'configuration');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => $this->app->databasePath('migrations'),
        ]);

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/authorization.php',
            'authorization'
        );

        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Installation
                InstallCommand::class
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
