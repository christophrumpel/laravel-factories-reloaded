<?php

namespace ChristophrumpelLaravelFactoriesReloadedTestsFactories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Models\Ingredient;
use Faker\Generator as Faker;


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

    public function getDefaults(Faker $faker): array
    {
        return [];
    }

}

