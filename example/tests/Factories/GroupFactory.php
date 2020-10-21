<?php

namespace ExampleAppTests\Factories;

use App\Models\Group;
use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Faker\Generator;

class GroupFactory extends BaseFactory
{
    protected string $modelClass = Group::class;

    public function create(array $extra = []): Group
    {
        return $this->build($extra);
    }

    public function make(array $extra = []): Group
    {
        return $this->build($extra, 'make');
    }

    public function getDefaults(Generator $faker): array
    {
        return [
            'name' => 'Family Rumpel',
            'size' => 2,
        ];
    }
}
