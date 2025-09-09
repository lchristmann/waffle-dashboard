<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WaffleEating;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Support\Carbon;

class WafflesEatenChart extends ChartWidget
{
    use HasFiltersSchema;

    protected static ?int $sort = 3;
    protected ?string $description = 'Waffles eaten per month';
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'md' => 6,
    ];

    public function getHeading(): string
    {
        $year = $this->filters['year'] ?? now()->year;

        return "Waffles Eaten ({$year})";
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function filtersSchema(Schema $schema): Schema
    {
        // Get distinct years from waffle eating data
        $years = WaffleEating::query()
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM date) AS year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->toArray();

        return $schema->components([
            Select::make('year')
                ->label('Year')
                ->options(array_combine($years, $years))
                ->default(now()->year)
                ->selectablePlaceholder(false),
        ]);
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
        $year = $this->filters['year'] ?? now()->year;
        $currentMonth = now()->month;

        // If the selected year is in the past, show all 12 months
        $maxMonth = $year === now()->year ? $currentMonth : 12;

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
