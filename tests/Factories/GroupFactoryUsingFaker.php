<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\FactoryInterface;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Faker\Generator;

class GroupFactoryUsingFaker extends BaseFactory
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

    public function getData(Generator $faker): array
    {
        return [
            'name' => $faker->name,
            'size' => $faker->randomNumber(),
            'mobile' => $faker->unique()->mobileNumber,
        ];
    }

}
