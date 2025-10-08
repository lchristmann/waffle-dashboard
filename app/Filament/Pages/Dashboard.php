<?php

namespace App\Filament\Pages;

use App\Filament\Actions\WaffleEatingBulkCreateAction;
use App\Filament\Actions\WaffleEatingCreateAction;
use App\Filament\Widgets\WaffleStatsOverview;
use App\Filament\Widgets\WafflesEatenChart;
use App\Filament\Widgets\WaffleDayParticipationsChart;
use App\Models\WaffleEating;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    public function getTitle(): string | Htmlable
    {
        $year = $this->filters['year'] ?? now()->year;

        return "Waffle Dashboard ({$year})";
    }

    public function getWidgets(): array
    {
        return [
            WaffleStatsOverview::class,
            WafflesEatenChart::class,
            WaffleDayParticipationsChart::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 12;
    }

    protected function getHeaderActions(): array
    {
        return [
            WaffleEatingCreateAction::make()->keyBindings(['command+shift+c', 'ctrl+shift+c'])->tooltip('Shortcut: Ctrl+Shift+C'),
            WaffleEatingBulkCreateAction::make()->keyBindings(['command+shift+b', 'ctrl+shift+b'])->tooltip('Shortcut: Ctrl+Shift+B'),
            FilterAction::make()->label('Change Year')
                ->schema([
                    Select::make('year')
                        ->label('Year')
                        ->options(function () {
                            $years = WaffleEating::query()
                                ->selectRaw('DISTINCT EXTRACT(YEAR FROM date) AS year')
                                ->orderByDesc('year')
                                ->pluck('year')
                                ->map(fn ($year) => (int) $year)
                                ->toArray();

                            return array_combine($years, $years);
                        })
                        ->default(now()->year)
                        ->selectablePlaceholder(false)
                        ->required(),
                ]),
        ];
    }
}
