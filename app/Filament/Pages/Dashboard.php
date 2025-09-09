<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\QuoteWidget;
use App\Filament\Widgets\WaffleStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Waffle Dashboard';

    /**
     * Register widgets for this dashboard.
     */
    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            QuoteWidget::class,

            WaffleStatsOverview::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 12;
    }
}
