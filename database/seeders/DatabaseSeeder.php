<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\WaffleEating;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 10 users
        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) {
                // For each user, create between 0 and 10 waffle eating records
                WaffleEating::factory()
                    ->count(rand(0, 10))
                    ->create([
                        'entered_by_user' => $user->id,
                    ]);
            });

        // Create the admin user and give him 5 waffle eating records
        $admin = User::factory()
            ->admin()
            ->create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin'),
            ]);

        WaffleEating::factory()
            ->count(5)
            ->create([
                'entered_by_user' => $admin->id,
            ]);
    }
}
