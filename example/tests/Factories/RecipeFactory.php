<?php

namespace ExampleAppTests\Factories;

use App\Models\Group;
use App\Models\Recipe;
use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Faker\Generator;

class RecipeFactory extends BaseFactory
{
    protected string $modelClass = Recipe::class;

    public function create(array $extra = []): Recipe
    {
        return $this->build($extra);
    }

    public function make(array $extra = []): Recipe
    {
        return $this->build($extra, 'make');
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
            'group_id' => Group::factory(),
        ]);
    }

}
