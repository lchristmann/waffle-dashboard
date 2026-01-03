<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\WaffleDay;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaffleEatingsRelationManager extends RelationManager
{
    protected static string $relationship = 'waffleEatings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')->required()->native(false)
                    ->maxDate(now())->minDate(now()->subYears(100))
                    ->default(function () {
                        return WaffleDay::mostRecentWithinDays(7)?->date ?? now();
                    }),
                TextInput::make('count')->required()->integer()->minValue(1)->maxValue(100)->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('count')->sortable(),
                TextColumn::make('enteredBy.name')->label('Entered by')->sortable()->toggleable(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['entered_by_user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['entered_by_user_id'] = auth()->id();
                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
