<?php

namespace App\Filament\Widgets;

use App\Models\RemoteWaffleEating;
use App\Models\WaffleEating;
use App\Traits\FormatsNumbers;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class WaffleStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use FormatsNumbers;

    protected static ?int $sort = 2;
    protected static bool $isLazy = false;
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Get year from global filter (fallback: current year)
        $year = $this->pageFilters['year'] ?? now()->year;
        $maxMonth = $year === now()->year ? now()->month : 12;

        /**
         * ---------------------------------------
         * Yearly totals
         * ---------------------------------------
         */
        $officeTotal = WaffleEating::yearTotal($year);
        $remoteTotal = RemoteWaffleEating::yearTotal($year);
        $total = $officeTotal + $remoteTotal;

        $officePct = $this->pf($officeTotal, $total);
        $remotePct = $this->pfc($officePct);

        $people = WaffleEating::participatorsInYear($year)
            ->merge(RemoteWaffleEating::participatorsInYear($year))
            ->unique()
            ->count();

        $days = WaffleEating::waffleDaysInYear($year)
            ->merge(RemoteWaffleEating::waffleDaysInYear($year))
            ->unique()
            ->count();

        /* ---------------------------------------------------------
         | Monthly aggregates (OFFICE)
         |---------------------------------------------------------- */
        $officeMonthly = WaffleEating::selectRaw('
                EXTRACT(MONTH FROM date) AS month,
                SUM(count) AS total_waffles,
                JSON_AGG(DISTINCT user_id) AS people,
                JSON_AGG(DISTINCT date::date) AS days
            ')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        /* ---------------------------------------------------------
         | Monthly aggregates (REMOTE, approved only)
         |---------------------------------------------------------- */
        $remoteMonthly = RemoteWaffleEating::selectRaw('
                EXTRACT(MONTH FROM date) AS month,
                SUM(count) AS total_waffles,
                JSON_AGG(DISTINCT user_id) AS people,
                JSON_AGG(DISTINCT date::date) AS days
            ')
            ->whereNotNull('approved_by')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        /* ---------------------------------------------------------
         | Build charts
         |---------------------------------------------------------- */
        $decode = fn ($value) => is_string($value) ? json_decode($value, true) : ($value ?? []);

        $totalByMonth  = [];
        $officeByMonth = [];
        $remoteByMonth = [];
        $peopleByMonth = [];
        $daysByMonth = [];

        for ($month = 1; $month <= $maxMonth; $month++) {
            $office = $officeMonthly[$month] ?? null;
            $remote = $remoteMonthly[$month] ?? null;

            $officeWaffles = $office->total_waffles ?? 0;
            $remoteWaffles = $remote->total_waffles ?? 0;

            $officePeople = $decode($office->people ?? []);
            $remotePeople = $decode($remote->people ?? []);

            $officeDays = $decode($office->days ?? []);
            $remoteDays = $decode($remote->days ?? []);

            $mergedPeople = collect($officePeople)->merge($remotePeople)->unique()->count();

            $mergedDays = collect($officeDays)->merge($remoteDays)->unique()->count();

            $officeByMonth[] = $officeWaffles;
            $remoteByMonth[] = $remoteWaffles;
            $totalByMonth[]  = $officeWaffles + $remoteWaffles;
            $peopleByMonth[] = $mergedPeople;
            $daysByMonth[]   = $mergedDays;

            // Insert debug snippet BN7C here
        }

        /* ---------------------------------------------------------
         | Stats
         |---------------------------------------------------------- */
        return [
            Stat::make("Waffles Eaten ({$year})", $total)
                ->description("Office + Remote")
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('primary')
                ->chart($totalByMonth),

            Stat::make("Office Waffles ({$year})", "{$officeTotal} ({$officePct}%)")
                ->description("Eaten at the office")
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('primary')
                ->chart($officeByMonth),

            Stat::make("Remote Waffles ({$year})", "$remoteTotal ({$remotePct}%)")
                ->description("Approved home-office waffles")
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('primary')
                ->chart($remoteByMonth),

            Stat::make("People Participated ({$year})", $people)
                ->description('Users who ate waffles')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart($peopleByMonth),

            Stat::make("Waffle Days ({$year})", $days)
                ->description('Number of days waffles were eaten')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->chart($daysByMonth),
        ];
    }
}
