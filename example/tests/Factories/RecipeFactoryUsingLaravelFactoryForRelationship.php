<?php

namespace ExampleAppTests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use App\Models\Group;
use App\Models\Recipe;
use Faker\Generator;

class RecipeFactoryUsingLaravelFactoryForRelationship extends BaseFactory
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
            'group_id' => Group::factory(),
        ];
    }
}
