<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;
use Faker\Generator;

class RecipeFactory extends BaseFactory
{

    protected string $modelClass = Recipe::class;

    public function create(array $extra = []): Recipe
    {
        return parent::create($extra);
    }

    public function make(array $extra = []): Recipe
    {
        return parent::make($extra);
    }

    public function getData(Generator $faker): array
    {
        return [
            'name' => 'Lasagne',
            'description' => 'Our family lasagne recipe.'
        ];
    }

}
