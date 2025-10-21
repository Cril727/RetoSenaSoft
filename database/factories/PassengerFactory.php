<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Passenger;

class PassengerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Passenger::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'first_surname' => fake()->word(),
            'second_surname' => fake()->word(),
            'names' => fake()->word(),
            'date_birth' => fake()->date(),
            'gender' => fake()->randomElement(["Man","Woman","Other"]),
            'type_document' => fake()->randomElement(["CC","TI","CE","Pasaporte"]),
            'document' => fake()->word(),
            'condicien_infante' => fake()->boolean(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
        ];
    }
}
