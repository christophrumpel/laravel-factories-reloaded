<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\Tests\factories\GroupFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\GroupFactoryUsingFaker;
use Christophrumpel\LaravelFactoriesReloaded\Tests\factories\RecipeFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class FactoryTest extends TestCase
{

    use RefreshDatabase;

    /** @test * */
    public function it_gives_you_a_new_factory_instance()
    {
        $this->assertInstanceOf(RecipeFactory::class, RecipeFactory::new());
        $this->assertInstanceOf(GroupFactory::class, GroupFactory::new());
    }

    /** @test * */
    public function it_gives_you_a_new_factory_model_instance()
    {
        $this->assertInstanceOf(Recipe::class, RecipeFactory::new()
            ->create());

        $this->assertInstanceOf(Group::class, GroupFactory::new()
            ->create());
    }

    /** @test * */
    public function it_gives_you_multiple_factory_model_instances()
    {
        $this->assertInstanceOf(Collection::class, RecipeFactory::new()
            ->times(3));
        $this->assertCount(3, RecipeFactory::new()
            ->times(3));

        $this->assertInstanceOf(Collection::class, GroupFactory::new()
            ->times(12));
        $this->assertCount(12, GroupFactory::new()
            ->times(12));
    }

    /** @test * */
    public function it_uses_default_model_data()
    {
        $this->assertEquals('Lasagne', RecipeFactory::new()
            ->create()->name);
        $this->assertEquals('Our family lasagne recipe.', RecipeFactory::new()
            ->create()->description);

        $this->assertEquals('Family Rumpel', GroupFactory::new()
            ->create()->name);
        $this->assertEquals(2, GroupFactory::new()
            ->create()->size);
    }

    /** @test * */
    public function it_lets_you_overwrite_default_data()
    {
        $this->assertEquals('Pizza', RecipeFactory::new()
            ->create(['name' => 'Pizza'])->name);

        $this->assertEquals(3, GroupFactory::new()
            ->create(['size' => 3])->size);
    }

    /** @test * */
    public function it_lets_you_use_faker_for_defining_data()
    {
        $this->assertIsString(GroupFactoryUsingFaker::new()->create()->name);
        $this->assertIsInt(GroupFactoryUsingFaker::new()->create()->size);
    }

}
