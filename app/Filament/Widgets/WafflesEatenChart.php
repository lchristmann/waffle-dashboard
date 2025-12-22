<?php

namespace App\Filament\Widgets;

use App\Models\RemoteWaffleEating;
use App\Models\WaffleEating;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WafflesEatenChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected ?string $description = 'Broken down by month';
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'md' => 6,
    ];

    public function getHeading(): string
    {
        $year = $this->pageFilters['year'] ?? now()->year;

        return "Waffles Eaten ({$year})";
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
         | Office waffles per month
         |------------------------------------------ */
        $officeMonthly = WaffleEating::selectRaw('EXTRACT(MONTH FROM date) AS month, SUM(count) AS total')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck('total', 'month'); // [month => total]

        /* -----------------------------------------
         | Remote waffles per month (approved only)
         |------------------------------------------ */
        $remoteMonthly = RemoteWaffleEating::selectRaw('EXTRACT(MONTH FROM date) AS month, SUM(count) AS total')
            ->whereYear('date', $year)
            ->whereNotNull('approved_by')
            ->groupBy('month')
            ->pluck('total', 'month'); // [month => total]

        /* -----------------------------------------
         | Build chart data
         |------------------------------------------ */
        $labels = [];
        $data = [];

        for ($month = 1; $month <= $maxMonth; $month++) {
            $labels[] = Carbon::create($year, $month)->format('M');

            $office = $officeMonthly[$month] ?? 0;
            $remote = $remoteMonthly[$month] ?? 0;

            $data[] = $office + $remote;

            // Insert debug snippet MVR5 here
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Waffles eaten',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.25,
                ],
            ],
        ];
    }
}
