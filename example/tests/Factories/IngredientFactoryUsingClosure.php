<?php

namespace ExampleAppTests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use App\Models\Ingredient;
use Faker\Generator;

class IngredientFactoryUsingClosure extends BaseFactory
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
            'description' => function (array $ingredient) {
                return "Super delicious {$ingredient['name']}";
            },
        ];
    }
}
