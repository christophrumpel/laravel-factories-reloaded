<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ExampleApp\Models\Group;
use ExampleApp\Models\Recipe;
use Faker\Generator as Faker;

$factory->define(Recipe::class, static function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->sentence,
    ];
});

$factory->state(Recipe::class, 'withGroup', static function () {
    return [
        'group_id' => factory(Group::class),
    ];
});

$factory->state(Recipe::class, 'withDifferentGroup', static function () {
    $group = factory(Group::class)->create();

    return [
        'group_id' => $group->id,
    ];
});

$factory->state(Recipe::class, 'withOneLineGroup', static function () {
    return ['group_id' => factory(Group::class)];
});

$factory->state(Recipe::class, 'withReturnGroupName', static function () {
    return ['group_name' => 'return all'];
});

$factory->state(Recipe::class, 'withSquareBracketGroupName', static function () {
    return ['group_name' => 'something];'];
});
