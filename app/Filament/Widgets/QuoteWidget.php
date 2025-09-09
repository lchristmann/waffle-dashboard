<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuoteWidget extends Widget
{
    protected string $view = 'filament.widgets.quote-widget';

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = [
        'md' => 6,
    ];
}
