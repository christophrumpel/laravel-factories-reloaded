<?php

namespace ExampleAppTests\Factories;

use App\Models\Recipe;
use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Faker\Generator;

class RecipeFactoryImmutable extends BaseFactory
{
    protected string $modelClass = Recipe::class;

    protected bool $immutable = true;

    public function create(array $extra = []): Recipe
    {
        return $this->build($extra);
    }

    public function make(array $extra = []): Recipe
    {
        return $this->build($extra, 'make');
    }

    public function getDefaults(Generator $faker): array
    {
        return [
            'name' => 'Lasagne',
            'description' => 'Our family lasagne recipe.',
        ];
    }

    public function pasta(): self
    {
        return $this->overwriteDefaults([
            'name' => 'Pasta',
        ]);
    }

    public function pizza(): self
    {
        return $this->overwriteDefaults([
            'name' => 'Pizza',
        ]);
    }
}
