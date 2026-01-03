<?php

namespace App\Filament\Actions;

use App\Models\User;
use App\Models\WaffleDay;
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
            ->label(__('Bulk Create'))
            ->modalHeading(__('Create Multiple Waffle Eatings'))
            ->schema([
                Repeater::make('waffle_eatings')
                    ->label(__('Waffle Eatings'))
                    ->addActionLabel(__('Add another'))
                    ->schema([
                        Grid::make()
                            ->schema([
                                DatePicker::make('date')->label(__('Date'))->required()->native(false)
                                    ->maxDate(now())->minDate(now()->subYears(100))
                                    ->default(function () {
                                        return WaffleDay::mostRecentWithinDays(7)?->date ?? now();
                                    }),
                                TextInput::make('count')->label(__('Count'))->required()->integer()->minValue(1)->maxValue(100)->default(1),

                                Select::make('user_id')
                                    ->label(__('Who ate'))
                                    ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
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
