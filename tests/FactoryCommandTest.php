<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use ExampleApp\Models\Group;
use ExampleApp\Models\Recipe;
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
            ->expectsQuestion('Please pick a model', $this->modelAnswer(Group::class))
            ->assertExitCode(0);

        $this->assertFileExists($this->exampleFactoriesPath('GroupFactory.php'));
    }

    /** @test */
    public function it_creates_factories_for_all_models(): void
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model', 'All')
            ->expectsQuestion('You have defined states in your old factories, do you want to import them to your new factory classes?', 'No')
            ->expectsOutput('GroupFactory, IngredientFactory, RecipeFactory were created successfully under the '.$this->exampleFactoriesNamespace().' namespace.')
            ->assertExitCode(0);

        $this->assertFileExists($this->exampleFactoriesPath('GroupFactory.php'));
        $this->assertFileExists($this->exampleFactoriesPath('IngredientFactory.php'));
        $this->assertFileExists($this->exampleFactoriesPath('RecipeFactory.php'));
    }

    /** @test */
    public function it_asks_user_to_integrate_old_factory_states_if_given_which_he_agrees_to(): void
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model', $this->modelAnswer(Recipe::class))
            ->expectsQuestion('You have defined states in your old factory, do you want to import them to your new factory class?',
                'Yes')
            ->assertExitCode(0);

        $this->assertFileExists($this->exampleFactoriesPath('RecipeFactory.php'));
        $this->assertTrue(method_exists($this->exampleFactoriesNamespace().'\RecipeFactory', 'withGroup'));
    }

    /** @test */
    public function it_creates_factories_with_immutable_states(): void
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model', $this->modelAnswer(Recipe::class))
            ->expectsQuestion('You have defined states in your old factory, do you want to import them to your new factory class?',
                'Yes')
            ->assertExitCode(0);

        $this->assertFileExists($this->exampleFactoriesPath('RecipeFactory.php'));
        $createdFactoryClassName = $this->exampleFactoriesNamespace().'\RecipeFactory';
        $recipeFactory = $createdFactoryClassName::new();


        $recipeOne = $recipeFactory->withGroup()->make();
        $recipeTwo = $recipeFactory->make();

        $this->assertNotNull($recipeOne->group_id);
        $this->assertNull($recipeTwo->group_id);
    }

    /** @test */
    public function it_asks_user_to_integrate_old_factory_states_if_given_which_he_denies(): void
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model', $this->modelAnswer(Recipe::class))
            ->expectsQuestion('You have defined states in your old factory, do you want to import them to your new factory class?',
                'No')
            ->assertExitCode(0);

        $this->assertFileExists($this->exampleFactoriesPath('RecipeFactory.php'));

        $generatedFactoryContent = file_get_contents($this->exampleFactoriesPath('RecipeFactory.php'));

        $this->assertFalse(Str::containsAll($generatedFactoryContent, [
            'public function withGroup(',
        ]));
    }

    /** @test */
    public function it_accepts_a_model_name_as_an_argument(): void
    {
        $this->artisan('make:factory-reloaded Ingredient')
            ->assertExitCode(0);

        $this->assertFileExists($this->exampleFactoriesPath('IngredientFactory.php'));
    }

    /** @test */
    public function it_asks_user_to_overwrite_which_he_agrees_to(): void
    {
        $factoryPath = $this->exampleFactoriesPath('GroupFactory.php');

        File::put($factoryPath,'test');
        $this->assertFileExists($factoryPath);

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
        $factoryPath = $this->exampleFactoriesPath('GroupFactory.php');

        File::put($factoryPath,'test');
        $this->assertFileExists($factoryPath);


        $this->artisan('make:factory-reloaded Group')
            ->expectsQuestion('This factory class already exists. Do you want to overwrite it?',
                'No')
            ->expectsOutput('No Files created.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile($factoryPath, 'test');
    }

    /** @test */
    public function it_does_not_ask_if_factory_already_exists_with_force(): void
    {
        $factoryPath = $this->exampleFactoriesPath('GroupFactory.php');

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
            ->expectsOutput($this->exampleFactoriesNamespace().'\IngredientFactory created successfully.');
    }

    /** @test */
    public function it_accepts_config_as_options(): void
    {
        Config::set('factories-reloaded.models_paths', '');
        Config::set('factories-reloaded.factories_path', '');
        Config::set('factories-reloaded.factories_namespace', '');

        try {
            $this->artisan('make:factory-reloaded Ingredient
                --models_path=A
                --factories_path=B
                --factories_namespace=C')
                ->assertExitCode(0);
        } catch (\Exception $exception) {}

        $this->assertContains('A', Config::get('factories-reloaded.models_paths'));
        $this->assertEquals('B', Config::get('factories-reloaded.factories_path'));
        $this->assertEquals('C', Config::get('factories-reloaded.factories_namespace'));
    }

    /** @test */
    public function it_can_find_models_in_option_passed_models_path(): void
    {
        $factoryPath = $this->exampleFactoriesPath('IngredientFactory.php');
        $this->assertFileNotExists($factoryPath);

        $this->artisan('make:factory-reloaded Ingredient')
            ->assertExitCode(0);

        $this->assertFileExists($factoryPath);
    }

    /** @test **/
    public function it_creates_folder_for_new_factories_if_not_given(): void
    {
        // Set factories path that does not exist yet
        Config::set('factories-reloaded.factories_path', $this->examplePath('tmp-factories'));

        $this->artisan('make:factory-reloaded Ingredient')
            ->assertExitCode(0);

        $this->assertFileExists($this->examplePath('tmp-factories/IngredientFactory.php'));
    }
}
