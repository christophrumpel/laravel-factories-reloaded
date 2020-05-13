<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\ExampleApp\Models\ModelsWithArrayState\Book::class, static function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});

$factory->state(
    \ExampleApp\Models\ModelsWithArrayState\Book::class,
    'customName',
    [
        'name' => 'custom-name',
    ]
);
