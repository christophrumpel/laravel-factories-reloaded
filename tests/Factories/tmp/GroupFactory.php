<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Faker\Generator as Faker;


class GroupFactory extends BaseFactory
{

    protected string $modelClass = Group::class;

    public function create(array $extra = []): Group
    {
        return parent::build($extra);
    }

    public function make(array $extra = []): Group
    {
        return parent::build($extra, 'make');
    }

    public function getData(Faker $faker): array
    {
        return [
            'name' => $faker->word,
            'size' => $faker->numberBetween(1, 10),
        ];

    }
    public function someState(): GroupFactory
    {
        $this->overwrite([
            'name' => 'cool',
        ]);
    
        return $this;
    }
}

