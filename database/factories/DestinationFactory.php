<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Destination;

class DestinationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Destination::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'city' => fake()->city(),
        ];
    }
}
