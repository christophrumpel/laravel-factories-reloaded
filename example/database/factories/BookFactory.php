<?php

namespace Database\Factories;

use App\Models\ModelsWithArrayState\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return ['name' => $this->faker->word];
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return Factory
     */
    public function customName(): Factory
    {
        return $this->state([
            'name' => 'custom-name',
        ]);
    }

}
