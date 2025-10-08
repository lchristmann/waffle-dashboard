<?php

namespace App\Filament\Resources\WaffleEatings\Pages;

use App\Filament\Actions\WaffleEatingBulkCreateAction;
use App\Filament\Resources\WaffleEatings\WaffleEatingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWaffleEatings extends ManageRecords
{
    protected static string $resource = WaffleEatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create')
                ->mutateDataUsing(function (array $data): array {
                    // Set the entered_by_user_id automatically
                    $data['entered_by_user_id'] = auth()->id();

                    return $data;
                }),
            WaffleEatingBulkCreateAction::make(),
        ];
    }
}
