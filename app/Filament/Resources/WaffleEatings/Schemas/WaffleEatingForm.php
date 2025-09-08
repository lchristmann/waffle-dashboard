<?php

namespace App\Filament\Resources\WaffleEatings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WaffleEatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')->required()->maxDate(now())->minDate(now()->subYears(100)),
                TextInput::make('count')->required()->numeric(),

                // Select user only on create
                Select::make('user_id')->label('Who ate')
                    ->relationship('user', 'name')
                    ->default(fn () => auth()->id())
                    ->visible(fn (string $context) => $context === 'create')
                    ->required(fn (string $context) => $context === 'create'),
            ]);
    }
}
