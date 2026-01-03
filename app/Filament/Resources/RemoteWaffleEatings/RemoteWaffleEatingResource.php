<?php

namespace App\Filament\Resources\RemoteWaffleEatings;

use App\Constants\StorageConstants;
use App\Filament\Resources\RemoteWaffleEatings\Pages\ManageRemoteWaffleEatings;
use App\Models\RemoteWaffleEating;
use App\Models\WaffleDay;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RemoteWaffleEatingResource extends Resource
{
    protected static ?string $model = RemoteWaffleEating::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?int $navigationSort = 50;

    public static function getNavigationLabel(): string
    {
        return __('Waffles (Remote)');
    }

    public static function getModelLabel(): string
    {
        return __('Waffle Eating') . ' (Remote)';
    }

    public static function getPluralLabel(): ?string
    {
        return __('Waffle Eatings') . ' (Remote)';
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

                FileUpload::make('image')
                    ->label(__('Proof Photo'))
                    ->image()
                    ->maxSize(2048)
                    ->validationMessages([
                        'max' => __('The image must not be larger than 2 MB.'),
                    ])
                    ->directory(StorageConstants::REMOTE_WAFFLES)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label(__('Date'))->date()->sortable(),
                TextColumn::make('count')->label(__('Count'))->sortable(),
                TextColumn::make('user.name')->label(__('User'))->searchable(),
                ImageColumn::make('image')->label(__('Image'))->disk('local'),
                IconColumn::make('approved_by')->label(__('Approved'))->boolean(),
                TextColumn::make('approvedBy.name')->label(__('Approved By'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label(__('Created at'))->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label(__('Updated at'))->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->orderBy('date', 'desc')
                    ->orderBy('id', 'desc');
            })
            ->filters([
                // Optional "My Records" filter
                Filter::make('ate')
                    ->label(__('My Records'))
                    ->query(fn (Builder $query) => $query->where('user_id', auth()->id()))
                    ->toggle(),

                // Optional manual filters
                SelectFilter::make('user')
                    ->label(__('Who ate'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('approvedBy')
                    ->label(__('Approved By'))
                    ->relationship('approvedBy', 'name', function (Builder $query) {
                        $query->where('is_admin', true);
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),

                // Admin-only Approve Action
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (RemoteWaffleEating $record) =>
                        auth()->user()->isAdmin() && !$record->isApproved()
                    )
                    ->action(fn (RemoteWaffleEating $record) =>
                        $record->update(['approved_by' => auth()->id()])
                    )
                    ->requiresConfirmation()
                    ->modalDescription(__('Have you reviewed the photo evidence?')),
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
            'index' => ManageRemoteWaffleEatings::route('/'),
        ];
    }
}
