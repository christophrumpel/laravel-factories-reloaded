<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\FactoryInterface;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Faker\Generator;

class GroupFactoryUsingFaker extends BaseFactory implements FactoryInterface
{

    /**
     * @string
     */
    protected $modelClass = Group::class;

    public function create(array $extra = []): Group
    {
        return parent::create($extra);
    }

    public function getData(Generator $faker): array
    {
        return [
            'name' => $faker->name,
            'size' => $faker->randomNumber(),
        ];
    }

}
