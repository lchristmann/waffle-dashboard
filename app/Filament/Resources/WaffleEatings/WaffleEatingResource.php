<?php

namespace App\Filament\Resources\WaffleEatings;

use App\Filament\Resources\WaffleEatings\Pages\CreateWaffleEating;
use App\Filament\Resources\WaffleEatings\Pages\EditWaffleEating;
use App\Filament\Resources\WaffleEatings\Pages\ListWaffleEatings;
use App\Filament\Resources\WaffleEatings\Schemas\WaffleEatingForm;
use App\Filament\Resources\WaffleEatings\Tables\WaffleEatingsTable;
use App\Models\WaffleEating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WaffleEatingResource extends Resource
{
    protected static ?string $model = WaffleEating::class;

    protected static ?string $navigationLabel = 'Waffles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    public static function form(Schema $schema): Schema
    {
        return WaffleEatingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaffleEatingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaffleEatings::route('/'),
            'create' => CreateWaffleEating::route('/create'),
            'edit' => EditWaffleEating::route('/{record}/edit'),
        ];
    }
}
