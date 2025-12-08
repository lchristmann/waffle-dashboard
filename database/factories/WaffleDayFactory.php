<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WaffleDay>
 */
class WaffleDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => now()->addDays(fake()->unique()->numberBetween(1, 100))->toDateString(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
