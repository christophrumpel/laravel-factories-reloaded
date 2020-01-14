<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Config;
use Christophrumpel\LaravelFactoriesReloaded\LaravelFactoriesReloadedServiceProvider;

class FactoryCommandTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('factories-reloaded.models_path', __DIR__.'/Models');
        Config::set('factories-reloaded.factories_path', __DIR__.'/Models');
    }

    protected function getPackageProviders($app)
    {
        return [LaravelFactoriesReloadedServiceProvider::class];
    }

    /** @test */
    public function it_fails_if_no_models_found()
    {
        // Set to a path with no models given
        Config::set('factories-reloaded.models_path', __DIR__.'/');

        $this->artisan('make:factoryReloaded')->expectsOutput('Sorry, but no models have been found.')->assertExitCode(0);
    }

    /** @test */
    public function it_creates_factory_for_chosen_model()
    {
        $this->artisan('make:factoryReloaded')->expectsQuestion('For which model do you want to create a Factory?',
            'Christophrumpel\LaravelFactoriesReloaded\Tests\Ingredient')->expectsOutput('Thank you! Ingredient it is.')->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/Models/IngredientFactory.php'));
    }

    /**
     * @test
     **/
    public function it_replaces_the_the_dummy_code_in_the_new_factory_class()
    {
        $this->artisan('make:factoryReloaded')->expectsQuestion('For which model do you want to create a Factory?',
            'Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Ingredient')->expectsOutput('Thank you! Ingredient it is.')->assertExitCode(0);

        $generatedFactoryContent = file_get_contents(__DIR__.'/Models/IngredientFactory.php');

        $this->assertTrue(Str::contains($generatedFactoryContent, [
            'IngredientFactory', 'Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Ingredient', 'Ingredient'
        ]));
    }

    /** @test */
    public function it_fails_if_factory_already_given()
    {
        $this->artisan('make:factoryReloaded')->expectsQuestion('For which model do you want to create a Factory?',
            'Christophrumpel\LaravelFactoriesReloaded\Tests\Recipe');

        $this->artisan('make:factoryReloaded')->expectsQuestion('For which model do you want to create a Factory?',
            'Christophrumpel\LaravelFactoriesReloaded\Tests\Recipe')->expectsOutput('Factory already exists!')->assertExitCode(0);
    }
}
