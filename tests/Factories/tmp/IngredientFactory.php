<?php

namespace ChristophrumpelLaravelFactoriesReloadedTestsFactories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Faker\Generator;
use Ingredient;

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

    public function getData(Generator $faker): array
    {
        return [];
    }

}

