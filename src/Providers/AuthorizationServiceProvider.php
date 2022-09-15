<?php

namespace Codestage\Authorization\Providers;

use Codestage\Authorization\Contracts\ITraitService;
use Codestage\Authorization\Services\TraitService;
use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * All the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        ITraitService::class => TraitService::class
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
            __DIR__ . '/../../database/migrations' => $this->app->databasePath('migrations'),
        ]);

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/authorization.php',
            'authorization'
        );

        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
