<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WaffleEating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class WaffleStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get year from global filter (fallback: current year)
        $year = $this->pageFilters['year'] ?? now()->year;
        $maxMonth = $year === now()->year ? now()->month : 12;

        $users = User::all();

        // Total yearly stats
        $totalWafflesEaten = $users->sum(fn($user) => $user->wafflesEatenInYear($year));
        $peopleParticipated = $users->filter(fn($user) => $user->wafflesEatenInYear($year) > 0)->count();
        $waffleDays = WaffleEating::waffleDaysInYear($year);

        // Monthly charts
        $wafflesByMonth = [];
        $peopleByMonth = [];
        $daysByMonth = [];

        for ($month = 1; $month <= $maxMonth; $month++) {
            $wafflesByMonth[] = $users->sum(fn($user) => $user->wafflesEatenInMonth($year, $month));
            $peopleByMonth[] = $users->filter(fn($user) => $user->wafflesEatenInMonth($year, $month) > 0)->count();
            $daysByMonth[] = WaffleEating::waffleDaysInMonth($year, $month);
        }

        return [
            Stat::make("Waffles Eaten ({$year})", $totalWafflesEaten)
                ->description("All waffles eaten that year")
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
