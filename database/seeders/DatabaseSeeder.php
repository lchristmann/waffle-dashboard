<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\WaffleDay;
use App\Models\WaffleEating;
use Carbon\Carbon;
use Carbon\CarbonInterface;
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
                        'user_id' => $user->id,                 // they ate
                        'entered_by_user_id' => $user->id,      // they entered them
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
                'user_id' => $admin->id,                     // admin ate them
                'entered_by_user_id' => $admin->id,          // admin entered them
            ]);

        // Create 20 random waffle eating records
        $allUsers = User::all();
        WaffleEating::factory()
            ->count(20)
            ->state(function () use ($allUsers) {
                return [
                    'user_id' => $allUsers->random()->id,           // eater
                    'entered_by_user_id' => $allUsers->random()->id, // who entered
                ];
            })
            ->create();

        // Create 5 waffle days (every 2nd Thursday, with one in the past)
        $nextThursday = Carbon::now()->next(CarbonInterface::THURSDAY);
        $dates = collect(range(-1, 3))->map(fn($i) => $nextThursday->copy()->addDays($i * 14));
        WaffleDay::factory()
            ->count(5)
            ->sequence(
                ...$dates->map(fn ($date) => [
                    'date' => $date,
                ])
            )
            ->create();
    }
}
