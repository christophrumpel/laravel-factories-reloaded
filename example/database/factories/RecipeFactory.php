<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ExampleApp\Models\Group;
use ExampleApp\Models\Recipe;
use Faker\Generator as Faker;

$factory->define(Recipe::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->sentence,
    ];
});

$factory->state(Recipe::class, 'withGroup', function () {
    return [
        'group_id' => factory(Group::class),
    ];
});

$factory->state(Recipe::class, 'withDifferentGroup', function () {
    $group = factory(Group::class)->create();

    return [
        'group_id' => $group->id,
    ];
});

$factory->state(Recipe::class, 'withOneLineGroup', function () {
    return ['group_id' => factory(Group::class)];
});

$factory->state(Recipe::class, 'withReturnGroupName', function () {
    return ['group_name' => 'return all'];
});

$factory->state(Recipe::class, 'withSquareBracketGroupName', function () {
    return ['group_name' => 'something];'];
});
