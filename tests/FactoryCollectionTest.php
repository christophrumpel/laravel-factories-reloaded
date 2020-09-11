<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\FactoryCollection;
use Christophrumpel\LaravelFactoriesReloaded\FactoryFile;
use Christophrumpel\LaravelFactoriesReloaded\LaravelFactoryExtractor;
use ExampleApp\Models\DifferentLocation\Comment;
use ExampleApp\Models\Group;
use ExampleApp\Models\Ingredient;
use ExampleApp\Models\Recipe;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FactoryCollectionTest extends TestCase
{
    /** @test * */
    public function it_can_be_created(): void
    {
        $factoryCollection = FactoryCollection::fromModels();

        $this->assertInstanceOf(FactoryCollection::class, $factoryCollection);
    }

    /** @test * */
    public function it_returns_collection_of_factory_files(): void
    {
        $factoryCollection = FactoryCollection::fromModels();

        $this->assertEqualsCanonicalizing($factoryCollection->all()->map->modelClass->toArray(), [
            Group::class,
            Recipe::class,
            Ingredient::class,
        ]);
    }

    /** @test * */
    public function it_returns_collection_of_factory_files_from_different_model_locations(): void
    {
        Config::set('factories-reloaded.models_paths', [
            $this->exampleAppPath('Models'),
            $this->exampleAppPath('Models/DifferentLocation'),
        ]);

        $factoryCollection = FactoryCollection::fromModels();

        $this->assertEqualsCanonicalizing($factoryCollection->all()->map->modelClass->toArray(), [
            Group::class,
            Recipe::class,
            Ingredient::class,
            Comment::class,
        ]);
    }

    /** @test * */
    public function it_returns_collection_of_factory_files_for_chosen_models(): void
    {
        $factoryCollection = FactoryCollection::fromModels([Group::class, Ingredient::class]);

        $this->assertEqualsCanonicalizing($factoryCollection->all()->map->modelClass->toArray(), [
            Group::class,
            Ingredient::class,
        ]);
    }

    /** @test * */
    public function it_writes_factory_classes_to_files(): void
    {
        FactoryCollection::fromModels()
            ->write();

        $this->assertFileExists($this->exampleFactoriesPath('RecipeFactory.php'));
        $this->assertFileExists($this->exampleFactoriesPath('GroupFactory.php'));
        $this->assertFileExists($this->exampleFactoriesPath('IngredientFactory.php'));
    }

    /** @test * */
    public function it_writes_factory_class_to_file_with_states(): void
    {
        File::delete($this->exampleFactoriesPath('RecipeFactory.php'));

        FactoryCollection::fromModels()
            ->write();

        $this->assertFileExists($this->exampleFactoriesPath('RecipeFactory.php'));

        $generatedRecipeFactoryContent = file_get_contents($this->exampleFactoriesPath('RecipeFactory.php'));

        $this->assertTrue(Str::containsAll($generatedRecipeFactoryContent, [
            'public function withGroup',
            'public function withDifferentGroup',
        ]));
    }

    /** @test * */
    public function it_writes_factory_class_to_file_without_states(): void
    {
        File::delete($this->exampleFactoriesPath('RecipeFactory.php'));

        FactoryCollection::fromModels()
            ->withoutStates()
            ->write();

        $this->assertFileExists($this->exampleFactoriesPath('RecipeFactory.php'));

        $generatedRecipeFactoryContent = file_get_contents($this->exampleFactoriesPath('RecipeFactory.php'));

        $this->assertFalse(Str::containsAll($generatedRecipeFactoryContent, [
            'public function withGroup',
            'public function withDifferentGroup',
        ]));
    }

    /** @test **/
    public function it_gives_you_factory_file_for_specific_model(): void
    {
        $factoryFile = FactoryCollection::fromModels()
            ->get(Group::class);

        $this->assertInstanceOf(FactoryFile::class, $factoryFile);
        $this->assertEquals($factoryFile->modelClass, Group::class);
    }
}
