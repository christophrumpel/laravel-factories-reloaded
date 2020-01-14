<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;

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
            'name'        => 'Lasagne',
            'description' => 'This is a classic one from my childhood.'
        ];
    }

}
