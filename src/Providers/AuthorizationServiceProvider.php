<?php

namespace Codestage\Authorization\Providers;

use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
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
