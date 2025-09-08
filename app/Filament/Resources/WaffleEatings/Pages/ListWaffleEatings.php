<?php

namespace App\Filament\Resources\WaffleEatings\Pages;

use App\Filament\Resources\WaffleEatings\WaffleEatingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaffleEatings extends ListRecords
{
    protected static string $resource = WaffleEatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
