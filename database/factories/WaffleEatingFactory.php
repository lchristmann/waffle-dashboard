<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WaffleEating>
 */
class WaffleEatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'count' => fake()->numberBetween(1, 10),
            'entered_by_user' => User::factory(), // creates a user if none is provided
        ];
    }
}
