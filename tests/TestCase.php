<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\LaravelFactoriesReloadedServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;

class TestCase extends \Orchestra\Testbench\TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        Config::set('factories-reloaded.models_path', __DIR__.'/Models');
        Config::set('factories-reloaded.models_namespace', 'Christophrumpel\LaravelFactoriesReloaded\Tests\Models');
        Config::set('factories-reloaded.factories_path', __DIR__.'/Factories/tmp');
        Config::set('factories-reloaded.factories_namespace', 'Christophrumpel\LaravelFactoriesReloaded\Tests\Factories');

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if (file_exists(__DIR__.'/Factories/tmp/GroupFactory.php')) {
            unlink(__DIR__.'/Factories/tmp/GroupFactory.php');
        }
    }

    /**
     * add the package provider
     *
     * @param $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [LaravelFactoriesReloadedServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
    }
}
