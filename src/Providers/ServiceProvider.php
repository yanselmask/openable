<?php

namespace Yanselmask\Openable\Providers;
use  Illuminate\Support\ServiceProvider as Provider;

class ServiceProvider extends  Provider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('openable.php'),
        ],'openable-config');

        $this->publishesMigrations([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ],'openable-migrations');
    }
}
