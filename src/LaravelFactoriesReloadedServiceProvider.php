<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Christophrumpel\LaravelFactoriesReloaded\Commands\MakeFactoryReloadedCommand;
use Illuminate\Support\ServiceProvider;

class LaravelFactoriesReloadedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-factories-reloaded');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-factories-reloaded');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/factories-reloaded.php' => config_path('factories-reloaded.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-factories-reloaded'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-factories-reloaded'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-factories-reloaded'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                MakeFactoryReloadedCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/factories-reloaded.php', 'factories-reloaded');

        // Register the main class to use with the facade
        //$this->app->singleton('laravel-factories-reloaded', function () {
        //    return new LaravelFactoriesReloaded;
        //});
    }
}
