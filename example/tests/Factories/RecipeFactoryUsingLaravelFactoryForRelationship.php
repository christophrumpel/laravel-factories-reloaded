<?php

namespace ExampleAppTests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use ExampleApp\Models\Group;
use ExampleApp\Models\Recipe;
use Faker\Generator;

class RecipeFactoryUsingLaravelFactoryForRelationship extends BaseFactory
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
            'group_id' => factory(Group::class),
        ];
    }
}
