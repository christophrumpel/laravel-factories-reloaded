<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Generator;

interface FactoryInterface
{

    public function create(array $extra = []);

    function getDefaults(Generator $faker): array;

}
