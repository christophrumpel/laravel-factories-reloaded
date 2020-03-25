<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\FactoryCollection;
use Christophrumpel\LaravelFactoriesReloaded\FactoryFile;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Ingredient;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;
use Illuminate\Support\Str;

class FactoryCollectionTest extends TestCase
{

    /** @test * */
    public function it_can_be_created()
    {
        $factoryCollection = FactoryCollection::fromModels();

        $this->assertInstanceOf(FactoryCollection::class, $factoryCollection);
    }

    /** @test * */
    public function it_returns_collection_of_factory_files()
    {
        $factoryCollection = FactoryCollection::fromModels();

        $this->assertEqualsCanonicalizing($factoryCollection->all()->map->modelClass->toArray(), [
            Group::class,
            Recipe::class,
            Ingredient::class,
        ]);
    }

    /** @test * */
    public function it_writes_factory_classes_to_files()
    {
        FactoryCollection::fromModels()
            ->write();

        $this->assertFileExists(__DIR__.'/../tests/tmp/RecipeFactory.php');
        $this->assertFileExists(__DIR__.'/../tests/tmp/GroupFactory.php');
        $this->assertFileExists(__DIR__.'/../tests/tmp/IngredientFactory.php');
    }

    /** @test * */
    public function it_writes_factory_class_to_file_with_states()
    {
        FactoryCollection::fromModels()
            ->write();

        $this->assertFileExists(__DIR__.'/../tests/tmp/RecipeFactory.php');

        $generatedRecipeFactoryContent = file_get_contents(__DIR__.'/../tests/tmp/RecipeFactory.php');

        $this->assertTrue(Str::containsAll($generatedRecipeFactoryContent, [
            'public function withGroup',
            'public function withDifferentGroup',
        ]));

    }

    /** @test * */
    public function it_writes_factory_class_to_file_without_states()
    {
        FactoryCollection::fromModels()
            ->withoutStates()
            ->write();

        $this->assertFileExists(__DIR__.'/../tests/tmp/RecipeFactory.php');

        $generatedRecipeFactoryContent = file_get_contents(__DIR__.'/../tests/tmp/RecipeFactory.php');

        $this->assertFalse(Str::containsAll($generatedRecipeFactoryContent, [
            'public function withGroup',
            'public function withDifferentGroup',
        ]));
    }

    /** @test **/
    public function it_gives_you_factory_file_for_specific_model()
    {
        $factoryFile = FactoryCollection::fromModels()
            ->get(Group::class);

        $this->assertInstanceOf(FactoryFile::class, $factoryFile);
        $this->assertEquals($factoryFile->modelClass, Group::class);
    }

}
