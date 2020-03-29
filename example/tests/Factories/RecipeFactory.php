<?php

namespace ExampleAppTests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
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
            'name' => $faker->name,
            'description' => 'Our family lasagne recipe.'
        ];
    }
}
