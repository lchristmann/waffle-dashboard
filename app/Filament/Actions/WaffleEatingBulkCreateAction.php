<?php

namespace App\Filament\Actions;

use App\Models\User;
use App\Models\WaffleEating;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

class WaffleEatingBulkCreateAction extends CreateAction
{
    public static function getDefaultName(): ?string
    {
        return 'waffleEatingBulkCreate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Bulk Create')
            ->modalHeading('Create Multiple Waffle Eatings')
            ->schema([
                Repeater::make('waffle_eatings')
                    ->addActionLabel('Add another')
                    ->schema([
                        Grid::make()
                            ->schema([
                                DatePicker::make('date')->required()->maxDate(now())->minDate(now()->subYears(100))->default(now()),
                                TextInput::make('count')->required()->integer()->minValue(1)->maxValue(100)->default(1),

                                Select::make('user_id')
                                    ->label('Who ate')
                                    ->options(User::orderBy('name')->pluck('name', 'id'))
                                    ->default(fn () => auth()->id())
                                    ->required(),
                            ])
                    ])
                    ->reorderable(false)
                    ->defaultItems(2),
            ])
            ->action(function (array $data): void {
                foreach ($data['waffle_eatings'] as $waffle) {
                    WaffleEating::create([
                        'date' => $waffle['date'],
                        'count' => $waffle['count'],
                        'user_id' => $waffle['user_id'],
                        'entered_by_user_id' => auth()->id(),
                    ]);
                }
            });
    }
}
