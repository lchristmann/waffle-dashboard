<?php

namespace App\Filament\Resources\WaffleEatings\Pages;

use App\Filament\Resources\WaffleEatings\WaffleEatingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaffleEating extends EditRecord
{
    protected static string $resource = WaffleEatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set the entered_by_user_id automatically
        $data['entered_by_user_id'] = auth()->id();

        return $data;
    }
}
