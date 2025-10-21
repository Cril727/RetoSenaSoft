<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Payer;
use App\Models\Reservation;

class ReservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => fake()->word(),
            'worth' => fake()->randomFloat(0, 0, 9999999999.),
            'number_of_positions' => fake()->numberBetween(-10000, 10000),
            'flight_id' => Flight::factory(),
            'passenger_id' => Passenger::factory(),
            'payer_id' => Payer::factory(),
        ];
    }
}
