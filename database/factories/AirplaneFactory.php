<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Airplane;

class AirplaneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Airplane::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'model' => fake()->word(),
            'amount' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
