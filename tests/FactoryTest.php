<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\CustomNamespace\NamespacedModelFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\GroupFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\GroupFactoryUsingFaker;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\RecipeFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\CustomNamespace\NamespacedModel;
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
    public function it_creates_you_a_new_factory_model_instance()
    {
        $this->assertInstanceOf(Recipe::class, RecipeFactory::new()
            ->create());

        $this->assertCount(1, Recipe::all());

        $this->assertInstanceOf(Group::class, GroupFactory::new()
            ->create());

        $this->assertCount(1, Group::all());

    }

    /** @test * */
    public function it_makes_you_a_new_factory_model_instance_without_storing_it()
    {
        $this->assertInstanceOf(Recipe::class, RecipeFactory::new()
            ->make());

        $this->assertCount(0, Recipe::all());

        $this->assertInstanceOf(Group::class, GroupFactory::new()
            ->make());

        $this->assertCount(0, Group::all());

    }

    /** @test * */
    public function it_gives_you_a_collection_of_created_factory_model_instances()
    {
        $this->assertInstanceOf(Collection::class, RecipeFactory::new()
            ->times(3)
            ->create());

        $this->assertCount(3, RecipeFactory::new()
            ->times(3)
            ->create());

        $this->assertInstanceOf(Collection::class, GroupFactory::new()
            ->times(12)
            ->create());

        $this->assertCount(12, GroupFactory::new()
            ->times(12)
            ->create());
    }

    /** @test * */
    public function collection_of_factory_models_has_unique_values()
    {
        $recipes = RecipeFactory::new()
            ->times(3)
            ->create();

        $this->assertNotEquals($recipes[0]->name, $recipes[1]->name);
        $this->assertNotEquals($recipes[1]->name, $recipes[2]->name);
    }

    /** @test * */
    public function it_gives_you_a_collection_of_made_factory_model_instances()
    {
        $this->assertInstanceOf(Collection::class, RecipeFactory::new()
            ->times(3)
            ->make());

        $this->assertCount(3, RecipeFactory::new()
            ->times(3)
            ->make());

        $this->assertInstanceOf(Collection::class, GroupFactory::new()
            ->times(12)
            ->make());

        $this->assertCount(12, GroupFactory::new()
            ->times(12)
            ->make());
    }

    /** @test * */
    public function it_uses_default_model_data()
    {
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
        $this->assertIsString(GroupFactoryUsingFaker::new()
            ->create()->name);
        $this->assertIsInt(GroupFactoryUsingFaker::new()
            ->create()->size);
    }

    /** @test * */
    public function it_lets_you_add_a_related_model()
    {
        $group = GroupFactory::new()
            ->with(Recipe::class, 'recipes')
            ->create();

        $this->assertEquals(1, $group->recipes->count());
        $this->assertInstanceOf(Recipe::class, $group->recipes->first());
    }

    /** @test * */
    public function it_lets_you_add_multiple_related_models()
    {
        $group = GroupFactory::new()
            ->with(Recipe::class, 'recipes', 4)
            ->create();

        $this->assertEquals(4, $group->recipes->count());
        $this->assertInstanceOf(Recipe::class, $group->recipes->first());
    }

    /** @test * */
    public function the_factory_is_immutable_when_adding_related_models(): void
    {
        $group = GroupFactory::new()
            ->with(Recipe::class, 'recipes', 4);

        $firstGroup = $group->with(Recipe::class, 'recipes')->create();
        $secondGroup = $group->create();

        $this->assertEquals(1, $firstGroup->recipes()->count());
        $this->assertEquals(4, $secondGroup->recipes()->count());
    }

    /** @test * */
    public function it_lets_you_use_namespaced_models()
    {
        $this->assertInstanceOf(NamespacedModel::class, NamespacedModelFactory::new()
            ->create());

        $this->assertCount(1, NamespacedModel::all());
    }

}
