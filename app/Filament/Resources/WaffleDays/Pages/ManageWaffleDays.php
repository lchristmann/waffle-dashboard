<?php

namespace App\Filament\Resources\WaffleDays\Pages;

use App\Filament\Resources\WaffleDays\WaffleDayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWaffleDays extends ManageRecords
{
    protected static string $resource = WaffleDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Create')),
        ];
    }
}
