<?php

namespace App\Filament\Resources\WaffleEatings;

use App\Filament\Resources\WaffleEatings\Pages\ManageWaffleEatings;
use App\Models\WaffleDay;
use App\Models\WaffleEating;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaffleEatingResource extends Resource
{
    protected static ?string $model = WaffleEating::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?int $navigationSort = 40;

    public static function getNavigationLabel(): string
    {
        return __('Waffles');
    }

    public static function getModelLabel(): string
    {
        return __('Waffle Eating');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Waffle Eatings');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')->label(__('Date'))->required()->native(false)
                    ->maxDate(now())->minDate(now()->subYears(100))
                    ->default(function () {
                        return WaffleDay::mostRecentWithinDays(7)?->date ?? now();
                    }),
                TextInput::make('count')->label(__('Count'))->required()->integer()->minValue(1)->maxValue(100)->default(1),

                // Select user only on create
                Select::make('user_id')->label(__('Who ate'))
                    ->relationship('user', 'name')
                    ->default(fn () => auth()->id())
                    ->visible(fn (string $context) => $context === 'create')
                    ->required(fn (string $context) => $context === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label(__('Date'))->date()->sortable(),
                TextColumn::make('count')->label(__('Count'))->sortable(),
                TextColumn::make('user.name')->label(__('User'))->searchable(),
                TextColumn::make('enteredBy.name')->label(__('Entered by'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label(__('Created at'))->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label(__('Updated at'))->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->orderBy('date', 'desc')
                    ->orderBy('id', 'desc');
            })
            ->filters([
                // Optional "My Records" filter (ate or entered)
                Filter::make('ate_or_entered')
                    ->label(__('My Records'))
                    ->query(fn ($query) => $query->where(function ($q) {
                        $q->where('user_id', auth()->id())
                            ->orWhere('entered_by_user_id', auth()->id());
                    }))
                    ->toggle(),

                // Optional manual filters
                SelectFilter::make('user')
                    ->label(__('Who ate'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('enteredBy')
                    ->label(__('Entered by'))
                    ->relationship('enteredBy', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        // Set the entered_by_user_id automatically
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

    public static function getPages(): array
    {
        return [
            'index' => ManageWaffleEatings::route('/'),
        ];
    }
}
