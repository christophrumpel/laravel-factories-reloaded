<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\RecipeFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;
use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;

class FactoryTest extends TestCase
{

    /** @test **/
    public function you_can_define_default_data_for_the_test_model()
    {
    	$recipe = RecipeFactory::create();

    	$this->assertInstanceOf(Recipe::class, $recipe);
    	$this->assertEquals('Lasagne', $recipe->name);
        $this->assertEquals('This is a classic one from my childhood.', $recipe->description);
    }

    /** @test **/
    public function you_can_create_multiple_test_models()
    {
        $recipes = RecipeFactory::createMultiple(5);

    	$this->assertInstanceOf(Collection::class, $recipes);
    	$this->assertEquals(5, $recipes->count());
    }

}
