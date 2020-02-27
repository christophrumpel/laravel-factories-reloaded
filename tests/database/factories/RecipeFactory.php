<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;
use Faker\Generator as Faker;

$factory->define(Recipe::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->sentence,
    ];
});

$factory->state(Recipe::class, 'withGroup', function() {
    return [
        'group_id' => factory(Group::class)
    ];
});
