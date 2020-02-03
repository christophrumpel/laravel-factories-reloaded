<?php

namespace Tests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Faker\Generator;

class GroupFactory extends BaseFactory
{

    protected string $modelClass = Group::class;

    public function create(array $extra = []): Group
    {
        return parent::create($extra);
    }

    public function getData(Generator $faker): array
    {
        return [];
    }

}

