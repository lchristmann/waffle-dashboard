<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RemoteWaffleEating>
 */
class RemoteWaffleEatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sourcePath = database_path('seeders/files/sample-waffle-image-' . (fake()->boolean ? '1' : '2') . '.jpg');
        $destinationPath = 'remote-waffles/' . uniqid() . '.jpg';

        Storage::put($destinationPath, file_get_contents($sourcePath));

        return [
            'user_id' => User::factory(), // creates a user if none is provided
            'date' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'count' => fake()->numberBetween(1, 10),
            'image' => $destinationPath,
            'approved_by' => null,
        ];
    }
}
