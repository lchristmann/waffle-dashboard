<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WaffleEating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WaffleStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Stats';
    protected ?string $description = 'Statistics about waffles eaten and participation this year';
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $year = now()->year;
        $currentMonth = now()->month;

        $users = User::all();

        // Total yearly stats
        $totalWafflesEaten = $users->sum(fn($user) => $user->wafflesEatenInYear($year));
        $peopleParticipated = $users->filter(fn($user) => $user->wafflesEatenInYear($year) > 0)->count();
        $waffleDays = WaffleEating::waffleDaysInYear($year);

        // Monthly charts
        $wafflesByMonth = [];
        $peopleByMonth = [];
        $daysByMonth = [];

        for ($month = 1; $month <= $currentMonth; $month++) {
            $wafflesByMonth[] = $users->sum(fn($user) => $user->wafflesEatenInMonth($year, $month));
            $peopleByMonth[] = $users->filter(fn($user) => $user->wafflesEatenInMonth($year, $month) > 0)->count();
            $daysByMonth[] = WaffleEating::waffleDaysInMonth($year, $month);
        }

        return [
            Stat::make("Total Waffles ({$year})", $totalWafflesEaten)
                ->description('All waffles eaten this year')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('primary')
                ->chart($wafflesByMonth),
            Stat::make("People Participated ({$year})", $peopleParticipated)
                ->description('Users who ate waffles')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart($peopleByMonth),
            Stat::make("Waffle Days ({$year})", $waffleDays)
                ->description('Number of days waffles were eaten')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->chart($daysByMonth),
        ];
    }
}
