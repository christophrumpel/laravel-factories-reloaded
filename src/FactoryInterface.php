<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Generator;

interface FactoryInterface
{
    public function create(array $extra = []);

    public function getDefaults(Generator $faker): array;
}
