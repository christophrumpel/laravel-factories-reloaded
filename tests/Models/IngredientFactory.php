<?php

namespace App\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Ingredient;

class IngredientFactory extends BaseFactory
{

    protected $className = Ingredient::class;

    public static function create(): Ingredient
    {
        return self::build();
    }

    protected function getData(): array
    {
        return [
        ];
    }

}

