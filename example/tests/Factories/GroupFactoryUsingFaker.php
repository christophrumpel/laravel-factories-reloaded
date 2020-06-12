<?php

namespace ExampleAppTests\Factories;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use ExampleApp\Models\Group;
use Faker\Generator;

class GroupFactoryUsingFaker extends BaseFactory
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
            'name' => $faker->name,
            'size' => $faker->randomNumber(),
            'mobile' => $faker->unique()->mobileNumber,
        ];
    }
}
