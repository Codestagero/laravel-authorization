<?php

namespace Codestage\Authorization\Providers;

use Codestage\Authorization\Console\Commands\InstallCommand;
use Codestage\Authorization\Console\Commands\MakePolicyCommand;
use Codestage\Authorization\Console\Commands\MakeRequirementCommand;
use Codestage\Authorization\Console\Commands\MakeRequirementHandlerCommand;
use Codestage\Authorization\Contracts\Services\{IAuthorizationCheckService, IAuthorizationService};
use Codestage\Authorization\Services\{AuthorizationCheckService, AuthorizationService};
use Illuminate\Foundation\Console\PolicyMakeCommand as BaseMakePolicyCommand;
use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * All the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        IAuthorizationCheckService::class => AuthorizationCheckService::class,
        IAuthorizationService::class => AuthorizationService::class,
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
                InstallCommand::class,

                // Make
                MakePolicyCommand::class,
                MakeRequirementCommand::class,
                MakeRequirementHandlerCommand::class,
            ]);

            // Override the base make:policy command
            $this->app->extend(BaseMakePolicyCommand::class, fn () => $this->app->make(MakePolicyCommand::class));
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
