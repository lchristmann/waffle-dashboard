<?php

namespace App\Filament\Widgets;

use App\Models\WaffleEating;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class WaffleDayParticipationsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected ?string $description = 'People having eaten a waffle per month';
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'md' => 6,
    ];

    public function getHeading(): string
    {
        $year = $this->pageFilters['year'] ?? now()->year;

        return "Waffle Day Participations ({$year})";
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

        $monthlyParticipations = WaffleEating::selectRaw('EXTRACT(MONTH FROM date) AS month, COUNT(DISTINCT user_id) AS total')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month'); // [month => total]

        $labels = [];
        $data = [];

        for ($month = 1; $month <= $maxMonth; $month++) {
            $labels[] = Carbon::create($year, $month)->format('M');
            $data[] = $monthlyParticipations[$month] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Participations',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.25,
                ],
            ],
        ];
    }
}
