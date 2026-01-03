<?php

namespace App\Filament\Resources\WaffleDays;

use App\Filament\Resources\WaffleDays\Pages\ManageWaffleDays;
use App\Models\WaffleDay;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaffleDayResource extends Resource
{
    protected static ?string $model = WaffleDay::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 70;

    public static function getNavigationLabel(): string
    {
        return __('Waffle Days');
    }

    public static function getModelLabel(): string
    {
        return __('Waffle Day');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Waffle Days');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label(__('Waffle Day Date'))
                    ->required()
                    ->unique()
                    ->native(false)
                    ->default(Carbon::now()->next(CarbonInterface::THURSDAY)->addWeek()),

                TextInput::make('note')
                    ->label(__('Note / Description'))
                    ->placeholder(__('Optional - e.g. shifted to Tuesday due to holiday')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label(__('Date'))->date()->sortable(),
                TextColumn::make('note')->label(__('Note / Description'))->limit(50),
                TextColumn::make('created_at')->label(__('Created'))->since(),
            ])
            ->defaultSort('date')
            ->filters([
                Filter::make('upcoming')
                    ->label(__('Upcoming Only'))
                    ->default()
                    ->query(fn(Builder $query): Builder =>
                        $query->whereDate('date', '>=', Carbon::today())
                    )
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ManageWaffleDays::route('/'),
        ];
    }
}
