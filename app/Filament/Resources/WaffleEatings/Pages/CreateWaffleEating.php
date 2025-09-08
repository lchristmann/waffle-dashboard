<?php

namespace App\Filament\Resources\WaffleEatings\Pages;

use App\Filament\Resources\WaffleEatings\WaffleEatingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaffleEating extends CreateRecord
{
    protected static string $resource = WaffleEatingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the entered_by_user_id automatically
        $data['entered_by_user_id'] = auth()->id();

        return $data;
    }
}
