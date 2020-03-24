<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;


use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Christophrumpel\LaravelFactoriesReloaded\LaravelFactoryExtractor;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class FactoryCollectionTest extends TestCase
{

    /** @test **/
    public function it_can_be_created_from_static_method()
    {
    	$factoryCollection = FactoryCollection::new();

    	$this->assertInstanceOf(FactoryCollection::class, $factoryCollection);
    }

    /** @test **/
    public function it_tells_if_default_factories_exist()
    {
        $factoryCollection = FactoryCollection::new();

        $this->assertTrue($factoryCollection->defaultFactoryExists(Group::class));
    }

    /** @test **/
    public function it_tells_if_new_factories_class_already_exists()
    {
        $factoryCollection = FactoryCollection::new();

        $this->assertTrue($factoryCollection->factoryClassExists(Group::class));
    }


    /** @test **/
    public function it_writes_factory_classes_to_files()
    {
        FactoryCollection::new()
            ->writeFactories();

        $this->assertFileExists(__DIR__.'/../Factoreis/tmp/RecipeFactory.php');
        $this->assertFileExists(__DIR__.'/../Factoreis/tmp/UserFactory.php');

    }

    /** @test **/
    public function it_writes_factory_class_to_file_with_states()
    {
        FactoryCollection::new()
            ->withDefaultFactoryStates()
            ->writeFactoryForModel(Recipe::class);

        $this->assertFileExists(__DIR__.'/../Factoreis/tmp/RecipeFactory.php');

        $generatedRecipeFactoryContent = file_get_contents(__DIR__.'/../Factoreis/tmp/RecipeFactory.php');

        $this->assertTrue(Str::containsAll($generatedRecipeFactoryContent, [
            'public function withGroup',
            'public function withDifferentGroup'
        ]));

    }

}
