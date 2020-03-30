<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\LaravelFactoriesReloadedServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected string $basePath;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->basePath = realpath(__DIR__ . '/../example');
    }

    public function setUp(): void
    {
        parent::setUp();

        Config::set('factories-reloaded.models_paths', [$this->exampleAppPath('Models')]);
        Config::set('factories-reloaded.vanilla_factories_path', $this->basePath . '/database/factories');
        Config::set('factories-reloaded.factories_path', $this->basePath . '/tests/Factories/tmp');
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories\Tmp');

        //$this->backupExampleAppFolder();

        $this->loadMigrationsFrom($this->basePath . '/database/migrations');
    }

    public function tearDown(): void
    {
        //$this->restoreExampleAppFolder();
        File::cleanDirectory($this->exampleFactoriesPath());

        parent::tearDown();
    }

    public function modelAnswer(string $model): string
    {
        $reflector = new ReflectionClass($model);

        return "<href=file://{$reflector->getFileName()}>{$reflector->getName()}</>";
    }

    public function examplePath($path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function exampleAppPath($path = ''): string
    {
        return $this->examplePath('app' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }

    public function exampleFactoriesPath($path = ''): string
    {
        return Config::get('factories-reloaded.factories_path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    protected function backupExampleAppFolder(): void
    {
        File::copyDirectory($this->basePath, __DIR__ . '/../backup');
    }

    protected function restoreExampleAppFolder(): void
    {
        File::moveDirectory(__DIR__ . '/../backup', $this->basePath, true);
        File::deleteDirectory(__DIR__ . '/../backup');
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
