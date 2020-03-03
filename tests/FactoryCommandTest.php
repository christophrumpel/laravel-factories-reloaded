<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class FactoryCommandTest extends TestCase
{

    /** @test */
    public function it_fails_if_no_models_found()
    {
        $this->expectException(\LogicException::class);

        // Set to a path with no models given
        Config::set('factories-reloaded.models_path', __DIR__ . '/');

        $this->artisan('make:factory-reloaded');
    }

    /** @test */
    public function it_creates_factory_for_chosen_model()
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://' . __DIR__ . '/Models/Group.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group</>')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__ . '/Factories/tmp/GroupFactory.php'));
    }

    /** @test * */
    public function it_replaces_the_the_dummy_code_in_the_new_factory_class()
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://' . __DIR__ . '/Models/Group.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group</>')
            ->assertExitCode(0);

        $generatedFactoryContent = file_get_contents(__DIR__ . '/Factories/tmp/GroupFactory.php');

        $this->assertTrue(Str::containsAll($generatedFactoryContent, [
            'GroupFactory',
            'Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group',
            'Group',
            'create(',
            'make(',
        ],));
    }

    /** @test */
    public function it_accepts_a_model_name_as_an_argument()
    {
        if (file_exists(__DIR__.'/Factories/tmp/IngredientFactory.php')) {
            unlink(__DIR__.'/Factories/tmp/IngredientFactory.php');
        }
        $this->assertFalse(File::exists(__DIR__ . '/Factories/tmp/IngredientFactory.php'));

        $this->artisan('make:factory-reloaded Ingredient')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__ . '/Factories/tmp/IngredientFactory.php'));
    }

    /** @test */
    public function it_fails_if_factory_already_exists_without_force()
    {
        $this->artisan('make:factory-reloaded Ingredient')
            ->expectsOutput('Factory already exists!');
    }

    /** @test */
    public function it_succeeds_if_factory_already_exists_with_force()
    {
        $this->artisan('make:factory-reloaded Ingredient --force')
            ->expectsOutput('Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\IngredientFactory created successfully.');
    }



    /** @test */
    public function it_accepts_config_as_options()
    {
        if (file_exists(__DIR__.'/Factories/tmp/IngredientFactory.php')) {
            unlink(__DIR__.'/Factories/tmp/IngredientFactory.php');
        }

        Config::set('factories-reloaded.models_path', '');
        Config::set('factories-reloaded.factories_path', '');
        Config::set('factories-reloaded.factories_namespace', '');


        $this->assertFalse(File::exists(__DIR__ . '/Factories/tmp/IngredientFactory.php'));

        $this->artisan('make:factory-reloaded Ingredient
                --models_path='.__DIR__.'/Models
                --factories_path='.__DIR__.'/Factories/tmp
                --factories_namespace=Christophrumpel\LaravelFactoriesReloaded\Tests\Factories
             ')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__ . '/Factories/tmp/IngredientFactory.php'));
    }

    /** @test */
    public function it_can_find_models_in_passed_models_path()
    {
        if (file_exists(__DIR__.'/Factories/tmp/IngredientFactory.php')) {
            unlink(__DIR__.'/Factories/tmp/IngredientFactory.php');
        }

        Config::set('factories-reloaded.models_path', '');
        Config::set('factories-reloaded.factories_path', '');
        Config::set('factories-reloaded.factories_namespace', '');

        $this->assertFalse(File::exists(__DIR__ . '/Factories/tmp/IngredientFactory.php'));

        $this->artisan('make:factory-reloaded Ingredient
                --models_path='.__DIR__.'/Models/Models
                --factories_path='.__DIR__.'/Factories/tmp
                --factories_namespace=Christophrumpel\LaravelFactoriesReloaded\Tests\Factories
             ')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__ . '/Factories/tmp/IngredientFactory.php'));
    }
}
