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
        $totalWafflesEaten = WaffleEating::whereYear('date', $year)->sum('count');
        $peopleParticipated = WaffleEating::whereYear('date', $year)->distinct('user_id')->count('user_id');
        $waffleDays = WaffleEating::waffleDaysInYear($year);

        // Monthly charts
        $monthlyAggregates = WaffleEating::selectRaw('
                EXTRACT(MONTH FROM date) AS month,
                SUM(count) AS total_waffles,
                COUNT(DISTINCT user_id) AS people,
                COUNT(DISTINCT date::date) AS days
            ')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $wafflesByMonth = [];
        $peopleByMonth = [];
        $daysByMonth = [];

        for ($month = 1; $month <= $maxMonth; $month++) {
            $aggregate = $monthlyAggregates[$month] ?? null;

            $wafflesByMonth[] = $aggregate->total_waffles ?? 0;
            $peopleByMonth[] = $aggregate->people ?? 0;
            $daysByMonth[] = $aggregate->days ?? 0;
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
