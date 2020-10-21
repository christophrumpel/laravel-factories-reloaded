<?php

namespace ExampleAppTests\Factories;

use App\Models\Ingredient;
use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Faker\Generator;

class IngredientFactory extends BaseFactory
{
    protected string $modelClass = Ingredient::class;

    public function create(array $extra = []): Ingredient
    {
        return parent::build($extra);
    }

    public function make(array $extra = []): Ingredient
    {
        return parent::build($extra, 'make');
    }

    public function getDefaults(Generator $faker): array
    {
        return [
            'name' => 'Pasta',
            'description' => 'Good pasta!',
        ];
    }
}
