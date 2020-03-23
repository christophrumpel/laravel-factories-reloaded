<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\FactoryInterface;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Faker\Generator;

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

    public function getDefaults(Generator $faker): array
    {
        return [
            'name' => 'Family Rumpel',
            'size' => 2,
        ];
    }

}
