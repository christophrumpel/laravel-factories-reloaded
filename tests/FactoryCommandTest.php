<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class FactoryCommandTest extends TestCase
{

    /** @test */
    public function it_fails_if_no_models_found(): void
    {
        $this->expectException(\LogicException::class);

        // Set to a path with no models given
        Config::set('factories-reloaded.models_paths', [__DIR__.'/']);

        $this->artisan('make:factory-reloaded');
    }

    /** @test */
    public function it_creates_factory_for_chosen_model(): void
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/Models/Group.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group</>')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/GroupFactory.php'));
    }

    /** @test */
    public function it_creates_factories_for_all_models(): void
    {
        $this->assertFalse(File::exists(__DIR__.'/tmp/GroupFactory.php'));
        $this->assertFalse(File::exists(__DIR__.'/tmp/IngredientFactory.php'));
        $this->assertFalse(File::exists(__DIR__.'/tmp/RecipeFactory.php'));

        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                'All')
            ->expectsQuestion('You have defined states in your old factories, do you want to import them to your new factory classes?', 'No')
            ->expectsOutput('GroupFactory, IngredientFactory, RecipeFactory were created successfully under the '.Config::get('factories-reloaded.factories_namespace').' namespace.')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/GroupFactory.php'));
        $this->assertTrue(File::exists(__DIR__.'/tmp/IngredientFactory.php'));
        $this->assertTrue(File::exists(__DIR__.'/tmp/RecipeFactory.php'));
    }

    /** @test **/
    public function it_imports_laravel_factory_default_model_data_if_given(): void
    {
        Config::set('factories-reloaded.factories_namespace',
            'Christophrumpel\LaravelFactoriesReloaded\Tests\Tmp');
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/Models/Recipe.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe</>')
            ->expectsQuestion('You have defined states in your old factory, do you want to import them to your new factory class?',
                'no')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/RecipeFactory.php'));

        $class_finder = new ClassFinder(new Filesystem());
        $factoryClassName = $class_finder->getFullyQualifiedClassNameFromFile(__DIR__.'/tmp/RecipeFactory.php');
        $recipe = $factoryClassName::new()->make();

        $this->assertNotEmpty($recipe->name);
        $this->assertNotEmpty($recipe->description);
        $this->assertIsNotObject($recipe->group_id);
        $this->assertIsNumeric($recipe->group_id);
    }

    /** @test */
    public function it_asks_user_to_integrate_old_factory_states_if_given_which_he_agrees_to(): void
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/Models/Recipe.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe</>')
            ->expectsQuestion('You have defined states in your old factory, do you want to import them to your new factory class?',
                'Yes')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/RecipeFactory.php'));

        $generatedFactoryContent = file_get_contents(__DIR__.'/tmp/RecipeFactory.php');

        $this->assertTrue(Str::containsAll($generatedFactoryContent, [
            'public function withGroup(',
        ]));
    }

    /** @test */
    public function it_asks_user_to_integrate_old_factory_states_if_given_which_he_denies(): void
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/Models/Recipe.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe</>')
            ->expectsQuestion('You have defined states in your old factory, do you want to import them to your new factory class?',
                'No')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/RecipeFactory.php'));

        $generatedFactoryContent = file_get_contents(__DIR__.'/tmp/RecipeFactory.php');

        $this->assertFalse(Str::containsAll($generatedFactoryContent, [
            'public function withGroup(',
        ]));
    }

    /** @test */
    public function it_accepts_a_model_name_as_an_argument(): void
    {
        $this->assertFalse(File::exists(__DIR__.'/tmp/IngredientFactory.php'));

        $this->artisan('make:factory-reloaded Ingredient')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/IngredientFactory.php'));
    }

    /** @test */
    public function it_asks_user_to_overwrite_which_he_agrees_to(): void
    {
        $factoryPath = __DIR__.'/tmp/GroupFactory.php';

        File::put($factoryPath,'test');
        $this->assertTrue(File::exists($factoryPath));

        $this->artisan('make:factory-reloaded Group')
            ->expectsQuestion('This factory class already exists. Do you want to overwrite it?',
                'Yes')
            ->assertExitCode(0);

        $generatedFactoryContent = file_get_contents($factoryPath);

        $this->assertFalse(Str::containsAll($generatedFactoryContent, [
            'test',
        ]));
    }

    /** @test */
    public function it_asks_user_to_overwrite_which_she_denies(): void
    {
        $factoryPath = __DIR__.'/tmp/GroupFactory.php';

        File::put($factoryPath,'test');
        $this->assertTrue(File::exists($factoryPath));
        $generatedFactoryContentBefore = file_get_contents($factoryPath);


        $this->artisan('make:factory-reloaded Group')
            ->expectsQuestion('This factory class already exists. Do you want to overwrite it?',
                'No')
            ->expectsOutput('No Files created.')
            ->assertExitCode(0);

        $generatedFactoryContentAfter = file_get_contents($factoryPath);

        $this->assertEquals($generatedFactoryContentBefore, $generatedFactoryContentAfter);
    }

    /** @test */
    public function it_does_not_ask_if_factory_already_exists_wit_force(): void
    {
        $factoryPath = __DIR__.'/tmp/GroupFactory.php';

        File::put($factoryPath,'test');
        $this->assertTrue(File::exists($factoryPath));

        $this->artisan('make:factory-reloaded Group --force')
            ->assertExitCode(0);

        $generatedFactoryContent = file_get_contents($factoryPath);
        $this->assertFalse(Str::containsAll($generatedFactoryContent, [
            'test',
        ]));
        $this->assertTrue(Str::containsAll($generatedFactoryContent, [
            'GroupFactory',
        ]));
    }

    /** @test */
    public function it_succeeds_if_factory_already_exists_with_force(): void
    {
        $this->artisan('make:factory-reloaded Ingredient');

        $this->artisan('make:factory-reloaded Ingredient --force')
            ->expectsOutput(Config::get('factories-reloaded.factories_namespace').'\IngredientFactory created successfully.');
    }

    /** @test */
    public function it_accepts_config_as_options(): void
    {
        if (file_exists(__DIR__.'/Factories/tmp/IngredientFactory.php')) {
            unlink(__DIR__.'/Factories/tmp/IngredientFactory.php');
        }

        Config::set('factories-reloaded.models_paths', '');
        Config::set('factories-reloaded.factories_path', '');
        Config::set('factories-reloaded.factories_namespace', '');

        $this->assertFalse(File::exists(__DIR__.'/Factories/tmp/IngredientFactory.php'));

        $this->artisan('make:factory-reloaded Ingredient
                --models_path='.__DIR__.'/Models
                --factories_path='.__DIR__.'/tmp
                --factories_namespace=Christophrumpel\LaravelFactoriesReloaded\Tests\Factories')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/IngredientFactory.php'));
    }

    /** @test */
    public function it_can_find_models_in_option_passed_models_path(): void
    {
        Config::set('factories-reloaded.models_paths', '');
        Config::set('factories-reloaded.factories_path', '');
        Config::set('factories-reloaded.factories_namespace', '');

        $this->assertFalse(File::exists(__DIR__.'/tmp/IngredientFactory.php'));

        $this->artisan('make:factory-reloaded Ingredient
                --models_path='.__DIR__.'/Models
                --factories_path='.__DIR__.'/tmp
                --factories_namespace=Christophrumpel\LaravelFactoriesReloaded\Tests\Factories
             ')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp/IngredientFactory.php'));
    }

    /** @test **/
    public function it_creates_folder_for_new_factories_if_not_given(): void
    {
        // Set factories path that does not exist yet
        Config::set('factories-reloaded.factories_path', __DIR__.'/tmp-factories');

        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/Models/Group.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group</>')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/tmp-factories/GroupFactory.php'));
    }
}
