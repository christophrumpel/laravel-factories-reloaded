<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;
use Faker\Generator as Faker;

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

    public function getDefaults(Faker $faker): array
    {
        return [
            'name' => $faker->word,
            'description' => $faker->sentence,
        ];

    }
    public function withGroup(): RecipeFactory
    {
        $this->overwrite([
            'group_id' => factory(Group::class)
        ]);
    
        return $this;
    }
    public function withDifferentGroup(): RecipeFactory
    {
        $this->overwrite([
            'group_id' => factory(Group::class)
        ]);
    
        return $this;
    }
}

