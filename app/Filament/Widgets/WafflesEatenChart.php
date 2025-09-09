<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class WafflesEatenChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected ?string $description = 'Waffles eaten per month';
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

        $labels = [];
        $data = [];

        for ($month = 1; $month <= $maxMonth; $month++) {
            $labels[] = Carbon::create($year, $month)->format('M');
            $data[] = User::all()->sum(fn ($user) => $user->wafflesEatenInMonth($year, $month));
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
