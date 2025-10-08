<?php

namespace App\Filament\Actions;

use App\Models\User;
use App\Models\WaffleEating;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

/*
 * Note: There's also a simplified create-action in the WaffleEating Filament resource
 * @see app/Filament/Resources/WaffleEatings/Pages/ManageWaffleEatings.php#getHeaderActions
 */
class WaffleEatingCreateAction extends CreateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Create')
            ->model(WaffleEating::class)
            ->schema([
                Grid::make()
                    ->schema([
                        DatePicker::make('date')->required()->maxDate(now())->minDate(now()->subYears(100))->default(now()),
                        TextInput::make('count')->required()->integer()->minValue(1)->maxValue(100)->default(1),

                        Select::make('user_id')
                            ->label('Who ate')
                            ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                            ->default(fn () => auth()->id())
                            ->required(),
                    ])
            ])
            ->mutateDataUsing(function (array $data): array {
                // Set the entered_by_user_id automatically
                $data['entered_by_user_id'] = auth()->id();

                return $data;
            });
    }
}
