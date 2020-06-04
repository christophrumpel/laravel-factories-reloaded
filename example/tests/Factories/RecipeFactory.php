<?php

namespace ExampleAppTests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use ExampleApp\Models\Group;
use ExampleApp\Models\Recipe;
use Faker\Generator;

class RecipeFactory extends BaseFactory
{
    protected string $modelClass = Recipe::class;

    public function create(array $extra = []): Recipe
    {
        return parent::build($extra);
    }

    public function make(array $extra = []): Recipe
    {
        return parent::build($extra, 'make');
    }

    public function getDefaults(Generator $faker): array
    {
        return [
            'name' => 'Lasagne',
            'description' => 'Our family lasagne recipe.',
        ];
    }

    public function withCustomDescription(): self
    {
        return tap(clone $this)->overwriteDefaults([
            'description' => 'my-desc',
        ]);
    }

    public function withCustomName(): self
    {
        return tap(clone $this)->overwriteDefaults([
            'name' => 'my-name',
        ]);
    }

    public function withGroup(): self
    {
        return tap(clone $this)->overwriteDefaults([
            'group_id' => GroupFactory::new(),
        ]);
    }

    public function withLaravelGroup(): self
    {
        return tap(clone $this)->overwriteDefaults([
            'group_id' => factory(Group::class),
        ]);
    }
}
