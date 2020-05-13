<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ExampleApp\Models\Group;
use Faker\Generator as Faker;

$factory->define(Group::class, static function (Faker $faker) {
    return [
        'name' => $faker->word,
        'size' => $faker->numberBetween(1, 10),
    ];
});
