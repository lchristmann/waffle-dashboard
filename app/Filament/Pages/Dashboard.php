<?php

namespace App\Filament\Pages;

use App\Filament\Actions\WaffleEatingBulkCreateAction;
use App\Filament\Actions\WaffleEatingCreateAction;
use App\Filament\Widgets\WaffleStatsOverview;
use App\Filament\Widgets\WafflesEatenChart;
use App\Filament\Widgets\WaffleDayParticipationsChart;
use App\Models\RemoteWaffleEating;
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
        return __('Waffle Dashboard') . " ({$year})";
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
            WaffleEatingCreateAction::make()->keyBindings(['command+shift+c', 'ctrl+shift+c'])->tooltip(__('Shortcut: Ctrl+Shift+C')),
            WaffleEatingBulkCreateAction::make()->keyBindings(['command+shift+b', 'ctrl+shift+b'])->tooltip(__('Shortcut: Ctrl+Shift+B')),
            FilterAction::make()->label(__('Change Year'))
                ->schema([
                    Select::make('year')
                        ->label(__('Year'))
                        ->options(function () {
                            $minOffice = WaffleEating::min('date');
                            $minRemote = RemoteWaffleEating::min('date');
                            $earliest = collect([$minOffice, $minRemote])->filter()->min();
                            $startYear = $earliest ? (int) date('Y', strtotime($earliest)) : now()->year;
                            $years = range(now()->year, $startYear);
                            return array_combine($years, $years);
                        })
                        ->default(now()->year)
                        ->selectablePlaceholder(false),
                ]),
        ];
    }
}
