<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use ExampleAppTests\Factories\GroupFactory;
use ExampleAppTests\Factories\GroupFactoryUsingFaker;
use ExampleAppTests\Factories\IngredientFactoryUsingClosure;
use ExampleAppTests\Factories\RecipeFactory;
use ExampleAppTests\Factories\RecipeFactoryUsingFactoryForRelationship;
use ExampleAppTests\Factories\RecipeFactoryUsingLaravelFactoryForRelationship;
use ExampleApp\Models\Group;
use ExampleApp\Models\Ingredient;
use ExampleApp\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test * */
    public function it_gives_you_a_new_factory_instance(): void
    {
        $this->assertInstanceOf(RecipeFactory::class, RecipeFactory::new());
        $this->assertInstanceOf(GroupFactory::class, GroupFactory::new());
    }

    /** @test * */
    public function it_creates_you_a_new_factory_model_instance(): void
    {
        $this->assertInstanceOf(Recipe::class, RecipeFactory::new()
            ->create());

        $this->assertCount(1, Recipe::all());

        $this->assertInstanceOf(Group::class, GroupFactory::new()
            ->create());

        $this->assertCount(1, Group::all());
    }

    /** @test * */
    public function it_makes_you_a_new_factory_model_instance_without_storing_it(): void
    {
        $this->assertInstanceOf(Recipe::class, RecipeFactory::new()
            ->make());

        $this->assertCount(0, Recipe::all());

        $this->assertInstanceOf(Group::class, GroupFactory::new()
            ->make());

        $this->assertCount(0, Group::all());
    }

    /** @test * */
    public function it_gives_you_a_collection_of_created_factory_model_instances(): void
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
    public function it_gives_you_a_collection_of_made_factory_model_instances(): void
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
    public function it_uses_default_model_data(): void
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
    public function it_lets_you_overwrite_default_data(): void
    {
        $this->assertEquals('Pizza', RecipeFactory::new()
            ->create(['name' => 'Pizza'])->name);

        $this->assertEquals(3, GroupFactory::new()
            ->create(['size' => 3])->size);
    }

    /** @test * */
    public function it_lets_you_overwrite_default_data_through_factory_methods(): void
    {
        $recipe = RecipeFactory::new()
            ->withCustomName()
            ->withCustomDescription()
            ->create();

        $this->assertEquals('my-desc', $recipe->description);
        $this->assertEquals('my-name', $recipe->name);
    }

    /** @test * */
    public function it_lets_you_overwrite_default_data_when_creating_multiple_instances(): void
    {
        $pancakes = RecipeFactory::new()
           ->times(5)
           ->create(['name' => 'Pancakes']);

        $this->assertEquals('Pancakes', $pancakes->first()->name);
    }

    /** @test * */
    public function it_lets_you_overwrite_default_data_when_making_multiple_instances(): void
    {
        $pancakes = RecipeFactory::new()
            ->times(5)
            ->make(['name' => 'Pancakes']);

        $this->assertEquals('Pancakes', $pancakes->first()->name);
    }

    /** @test */
    public function it_does_not_fill_field_that_is_not_fillable_according_to_config(): void
    {
        config()->set('factories-reloaded.unguard_models', false);

        $group = GroupFactory::new()->create([
            'mobile' => 'fake-mobile',
        ]);

        $this->assertFalse($group->isFillable('mobile'));
        $this->assertNull($group->mobile);
    }

    /** @test */
    public function it_fills_field_that_is_not_fillable_according_to_config(): void
    {
        config()->set('factories-reloaded.unguard_models', true);

        $group = GroupFactory::new()->create([
            'mobile' => 'fake-mobile',
        ]);

        $this->assertFalse($group->isFillable('mobile'));
        $this->assertIsString($group->mobile);
    }

    /** @test * */
    public function it_lets_you_use_faker_for_defining_data(): void
    {
        // We need `unguard_models` to be `true` here because `mobile` is not fillable.
        config()->set('factories-reloaded.unguard_models', true);

        // Set local to en_SG so that we can test that also local faker data is being used
        // Faker unique mobile number exists for en_SG
        Config::set('app.faker_locale', 'en_SG');

        $group = GroupFactoryUsingFaker::new()->create();

        $this->assertIsString($group->name);
        $this->assertIsInt($group->size);
        $this->assertIsString($group->mobile);
    }

    /** @test * */
    public function it_lets_you_use_a_closure_for_defining_default_data(): void
    {
        $ingredient = IngredientFactoryUsingClosure::new()->create();

        $this->assertIsString($ingredient->name);
        $this->assertIsString($ingredient->description);
        $this->assertEquals($ingredient->name, 'Pasta');
        $this->assertEquals($ingredient->description, 'Super delicious Pasta');
    }

    /** @test * */
    public function it_lets_you_use_a_closure_for_overriding_default_data(): void
    {
        $ingredient = IngredientFactoryUsingClosure::new()->create([
            'name' => 'Basil',
            'description' => fn (array $ingredient) => "Super delicious {$ingredient['name']}",
        ]);

        $this->assertIsString($ingredient->name);
        $this->assertIsString($ingredient->description);
        $this->assertEquals($ingredient->name, 'Basil');
        $this->assertEquals($ingredient->description, 'Super delicious Basil');
    }

    /** @test * */
    public function it_lets_you_add_a_related_model(): void
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $group = GroupFactory::new()
            ->with(Recipe::class, 'recipes')
            ->create();

        $this->assertEquals(1, $group->recipes->count());
        $this->assertInstanceOf(Recipe::class, $group->recipes->first());
    }

    /** @test * */
    public function it_lets_you_add_a_related_model_with_make(): void
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $group = GroupFactory::new()
            ->with(Recipe::class, 'recipes')
            ->make();

        $this->assertEquals(1, $group->recipes->count());
        $this->assertEquals(0, Recipe::count());
        $this->assertEquals(0, Group::count());
    }

    /** @test * */
    public function it_lets_you_add_multiple_related_models(): void
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $group = GroupFactory::new()
            ->with(Recipe::class, 'recipes', 4)
            ->create();

        $this->assertEquals(4, $group->recipes->count());
        $this->assertInstanceOf(Recipe::class, $group->recipes->first());
    }

    /** @test * */
    public function the_factory_is_immutable_when_adding_related_models(): void
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $group = GroupFactory::new()
            ->with(Recipe::class, 'recipes', 4);

        $firstGroup = $group->with(Recipe::class, 'recipes')->create();
        $secondGroup = $group->create();

        $this->assertEquals(5, $firstGroup->recipes()->count());
        $this->assertEquals(4, $secondGroup->recipes()->count());
    }

    /** @test */
    public function it_works_with_factory_as_relationship(): void
    {
        $recipe = RecipeFactoryUsingFactoryForRelationship::new()->create();

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals(1, $recipe->group_id);
        $this->assertCount(1, Recipe::all());
        $this->assertCount(1, Group::all());
    }

    /** @test */
    public function it_works_with_factory_as_relationship_in_state(): void
    {
        $recipe = RecipeFactory::new()->withGroup()->create();

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals(1, $recipe->group_id);
        $this->assertCount(1, Recipe::all());
        $this->assertCount(1, Group::all());
    }

    /** @test */
    public function it_works_with_factory_as_relationship_in_extras(): void
    {
        $recipe = RecipeFactory::new()->create(['group_id' => GroupFactory::new()]);

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals(1, $recipe->group_id);
        $this->assertCount(1, Recipe::all());
        $this->assertCount(1, Group::all());
    }

    /** @test */
    public function it_works_with_laravel_factory_as_relationship(): void
    {
        $recipe = RecipeFactoryUsingLaravelFactoryForRelationship::new()->create();

        $this->assertEquals(1, $recipe->group_id);
        $this->assertCount(1, Recipe::all());
        $this->assertCount(1, Group::all());
    }

    /** @test */
    public function it_works_with_laravel_factory_as_relationship_in_state(): void
    {
        $recipe = RecipeFactory::new()->withLaravelGroup()->create();

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals(1, $recipe->group_id);
        $this->assertCount(1, Recipe::all());
        $this->assertCount(1, Group::all());
    }

    /** @test */
    public function it_works_with_laravel_factory_as_relationship_in_extras(): void
    {
        $recipe = RecipeFactory::new()->create(['group_id' => factory(Group::class)]);

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals(1, $recipe->group_id);
        $this->assertCount(1, Recipe::all());
        $this->assertCount(1, Group::all());
    }

    /** @test */
    public function it_works_with_factory_as_relationship_for_creating_multiple_models(): void
    {
        $recipes = RecipeFactoryUsingFactoryForRelationship::new()->times(4)->create();

        $this->assertInstanceOf(Collection::class, $recipes);
        $this->assertCount(4, Recipe::all());
        $this->assertCount(4, Group::all());
    }

    /** @test */
    public function it_lets_you_add_related_models_when_creating_multiple()
    {
        $groups = GroupFactory::new()
            ->with(Recipe::class, 'recipes', 5, true)
            ->times(3)
            ->create();

        $this->assertCount(3, $groups);
        $this->assertCount(15, Recipe::all());
        $groups->each(function (Group $group) {
            $this->assertCount(5, $group->recipes);
        });
    }

    /** @test */
    public function it_lets_you_create_many_related_models_at_once()
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $recipe = RecipeFactory::new()
            ->with(Group::class, 'group')
            ->with(Ingredient::class, 'ingredients', 3)
            ->create();

        $this->assertCount(1, Group::all());
        $this->assertCount(3, Ingredient::all());
        $this->assertTrue($recipe->group()->exists());
        $this->assertEquals(3, $recipe->ingredients()->count());
    }

    /** @test */
    public function it_lets_you_make_many_related_models_at_once()
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $recipe = RecipeFactory::new()
            ->with(Group::class, 'group')
            ->with(Ingredient::class, 'ingredients', 3)
            ->make();

        $this->assertCount(0, Group::all());
        $this->assertCount(0, Ingredient::all());

        $this->assertInstanceOf(Group::class, $recipe->group);
        $this->assertEquals(3, $recipe->ingredients->count());
        $this->assertInstanceOf(Ingredient::class, $recipe->ingredients->first());
    }

    /** @test */
    public function it_lets_you_stack_relations()
    {
        Config::set('factories-reloaded.factories_namespace', 'ExampleAppTests\Factories');

        $recipe = RecipeFactory::new()
            ->with(Ingredient::class, 'ingredients')
            ->with(Ingredient::class, 'ingredients', 3)
            ->create();

        $this->assertCount(4, Ingredient::all());
        $this->assertEquals(4, $recipe->ingredients()->count());
    }
}
