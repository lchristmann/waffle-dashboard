<?php

namespace Database\Factories;

use App\Constants\StorageConstants;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GalleryImage>
 */
class GalleryImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws ConnectionException
     */
    public function definition(): array
    {
        $relativePath = StorageConstants::GALLERY_IMAGES . '/' . uniqid() . '.jpg';
        $response = Http::get("https://picsum.photos/640/480.jpg");
        Storage::put($relativePath, $response->body());

        return [
            'date' => fake()->dateTimeBetween('-20 months')->format('Y-m-d'),
            'path' => $relativePath,
            'user_id' => User::factory(),
        ];
    }
}
