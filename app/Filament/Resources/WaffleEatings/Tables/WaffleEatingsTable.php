<?php

namespace App\Filament\Resources\WaffleEatings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WaffleEatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('count')->sortable(),
                TextColumn::make('user.name')->label('Who ate')->searchable(),
                TextColumn::make('enteredBy.name')->label('Entered by')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                // Default "My Records" filter (ate or entered)
                Filter::make('ate_or_entered')
                    ->label('My Records')
                    ->query(fn ($query) => $query->where(function ($q) {
                        $q->where('user_id', auth()->id())
                            ->orWhere('entered_by_user_id', auth()->id());
                    }))
                    ->default(),

                // Optional manual filters
                SelectFilter::make('user')
                    ->label('Who ate')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('enteredBy')
                    ->label('Entered by')
                    ->relationship('enteredBy', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }
}
