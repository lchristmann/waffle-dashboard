<?php

namespace App\Filament\Widgets;

use App\Models\RemoteWaffleEating;
use App\Models\WaffleEating;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WaffleDayParticipationsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'md' => 6,
    ];

    public function getHeading(): string
    {
        $year = $this->pageFilters['year'] ?? now()->year;
        return __('Waffle Day Participations') . " ({$year})";
    }

    public function getDescription(): ?string
    {
        return __('People having eaten a waffle per month');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks' => [
                    'precision' => 0,
                ],
            ],
        ],
    ];

    protected function getData(): array
    {
        $year = $this->pageFilters['year'] ?? now()->year;
        $maxMonth = $year === now()->year ? now()->month : 12;

        /* -----------------------------------------
         | Office participations per month
         |------------------------------------------ */
        $officeMonthly = WaffleEating::selectRaw('
                EXTRACT(MONTH FROM date) AS month,
                JSON_AGG(DISTINCT user_id) AS people
            ')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        /* -----------------------------------------
         | Remote participations per month (approved)
         |------------------------------------------ */
        $remoteMonthly = RemoteWaffleEating::selectRaw('
                EXTRACT(MONTH FROM date) AS month,
                JSON_AGG(DISTINCT user_id) AS people
            ')
            ->whereYear('date', $year)
            ->whereNotNull('approved_by')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        /* -----------------------------------------
         | Build chart data
         |------------------------------------------ */
        $decode = fn ($value) => is_string($value) ? json_decode($value, true) : ($value ?? []);

        $labels = [];
        $data = [];

        for ($month = 1; $month <= $maxMonth; $month++) {
            $labels[] = Carbon::create($year, $month)->format('M');

            $officePeople = $decode($officeMonthly[$month]->people ?? []);
            $remotePeople = $decode($remoteMonthly[$month]->people ?? []);

            $uniquePeopleCount = collect($officePeople)->merge($remotePeople)->unique()->count();

            $data[] = $uniquePeopleCount;

            // Insert debug snippet Q2ST here
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Participations'),
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.25,
                ],
            ],
        ];
    }
}
