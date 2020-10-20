<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Recipe::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];
    }

    public function withGroup(): Factory
    {
        return $this->state([
            'group_id' => Group::factory(),
        ]);
    }

    public function withDifferentGroup(): Factory
    {
        $group = GroupFactory::new()
            ->create();

        return $this->state([
            'group_id' => $group->id,
        ]);
    }

    public function withOneLineGroup(): Factory
    {
        return $this->state(['group_id' => Group::factory()]);
    }

    public function withReturnGroupName(): Factory
    {
        return $this->state(['group_name' => 'return all']);
    }

    public function withClosureGroupName(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => $attributes['name'] . ' New Name',
            ];
        });
    }

    public function withSquareBracketGroupName(): Factory
    {
        return $this->state(['group_name' => 'something];']);
    }

}

