<?php

namespace App\Filament\Resources\RemoteWaffleEatings\Pages;

use App\Filament\Resources\RemoteWaffleEatings\RemoteWaffleEatingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRemoteWaffleEatings extends ManageRecords
{
    protected static string $resource = RemoteWaffleEatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create')
                ->mutateDataUsing(fn (array $data) => [
                    ...$data,
                    'user_id' => auth()->id(),
                ]),
        ];
    }
}
