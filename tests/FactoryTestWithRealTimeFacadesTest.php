<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use ExampleApp\Models\Group;
use ExampleApp\Models\Recipe;
use Facades\ExampleAppTests\Factories\GroupFactory;
use Facades\ExampleAppTests\Factories\GroupFactoryUsingFaker;
use Facades\ExampleAppTests\Factories\RecipeFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class FactoryTestWithRealTimeFacadesTest extends TestCase
{
    use RefreshDatabase;

    /** @test * */
    public function it_creates_you_a_new_factory_model_instance(): void
    {
        $this->assertInstanceOf(Recipe::class, RecipeFactory::create());

        $this->assertCount(1, Recipe::all());

        $this->assertInstanceOf(Group::class, GroupFactory::create());

        $this->assertCount(1, Group::all());

    }

    /** @test * */
    public function it_makes_you_a_new_factory_model_instance_without_storing_it(): void
    {
        $this->assertInstanceOf(Recipe::class, RecipeFactory::make());

        $this->assertCount(0, Recipe::all());

        $this->assertInstanceOf(Group::class, GroupFactory::make());

        $this->assertCount(0, Group::all());

    }

    /** @test * */
    public function it_gives_you_a_collection_of_created_factory_model_instances(): void
    {
        $this->assertInstanceOf(Collection::class, RecipeFactory::times(3)->create());

        $this->assertCount(3, RecipeFactory::times(3)->create());

        $this->assertInstanceOf(Collection::class, GroupFactory::times(12)->create());

        $this->assertCount(12, GroupFactory::times(12)->create());
    }


    /** @test * */
    public function it_gives_you_a_collection_of_made_factory_model_instances(): void
    {
        $this->assertInstanceOf(Collection::class, RecipeFactory::times(3)->make());

        $this->assertCount(3, RecipeFactory::times(3)->make());

        $this->assertInstanceOf(Collection::class, GroupFactory::times(12)->make());

        $this->assertCount(12, GroupFactory::times(12)->make());
    }

    /** @test * */
    public function it_uses_default_model_data(): void
    {
        $this->assertEquals('Lasagne', RecipeFactory::create()->name);

        $this->assertEquals('Our family lasagne recipe.', RecipeFactory::create()->description);

        $this->assertEquals('Family Rumpel', GroupFactory::create()->name);

        $this->assertEquals(2, GroupFactory::create()->size);
    }

    /** @test * */
    public function it_lets_you_overwrite_default_data(): void
    {
        $this->assertEquals('Pizza', RecipeFactory::create(['name' => 'Pizza'])->name);

        $this->assertEquals(3, GroupFactory::create(['size' => 3])->size);
    }

    /** @test * */
    public function it_lets_you_use_faker_for_defining_data(): void
    {
        // Set local to en_SG so that we can test that also local faker data is being used
        // Faker unique mobile number exists for en_SG
        Config::set('app.faker_locale', 'en_SG');

        $this->assertIsString(GroupFactoryUsingFaker::create()->name);

        $this->assertIsInt(GroupFactoryUsingFaker::create()->size);
    }

    /** @test * */
    public function it_lets_you_add_a_related_model(): void
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $group = GroupFactory::with(Recipe::class, 'recipes')->create();

        $this->assertEquals(1, $group->recipes->count());
        $this->assertInstanceOf(Recipe::class, $group->recipes->first());
    }

    /** @test * */
    public function it_lets_you_add_multiple_related_models(): void
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $group = GroupFactory::with(Recipe::class, 'recipes', 4)->create();

        $this->assertEquals(4, $group->recipes->count());
        $this->assertInstanceOf(Recipe::class, $group->recipes->first());
    }

    /** @test * */
    public function the_factory_is_immutable_when_adding_related_models(): void
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $group = GroupFactory::with(Recipe::class, 'recipes', 4);

        $firstGroup = $group->with(Recipe::class, 'recipes')->create();
        $secondGroup = $group->create();

        $this->assertEquals(1, $firstGroup->recipes()->count());
        $this->assertEquals(4, $secondGroup->recipes()->count());
    }
}
