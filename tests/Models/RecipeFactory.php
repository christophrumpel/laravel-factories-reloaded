<?php

namespace App\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Recipe;

class RecipeFactory extends BaseFactory
{

    protected $className = Recipe::class;

    public static function create(): Recipe
    {
        return self::build();
    }

    protected function getData(): array
    {
        return [
        ];
    }

}

