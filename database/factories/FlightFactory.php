<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Airplane;
use App\Models\Destination;
use App\Models\Flight;
use App\Models\Origin;

class FlightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Flight::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'hour' => fake()->time(),
            'ability' => fake()->numberBetween(-10000, 10000),
            'price' => fake()->randomFloat(0, 0, 9999999999.),
            'destination_id' => Origin::factory()->create()->city,
            'origin_id' => Destination::factory(),
            'avion_id' => Airplane::factory(),
        ];
    }
}
