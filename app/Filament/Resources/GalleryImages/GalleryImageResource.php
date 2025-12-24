<?php

namespace App\Filament\Resources\GalleryImages;

use App\Constants\StorageConstants;
use App\Filament\Resources\GalleryImages\Pages\ManageGalleryImages;
use App\Models\GalleryImage;
use App\Models\WaffleDay;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?string $navigationLabel = 'Image Uploads';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')->required()->native(false)
                    ->maxDate(now())->minDate(now()->subYears(100))
                    ->default(function () {
                        return WaffleDay::mostRecent()?->date ?? now();
                    }),

                FileUpload::make('path')
                    ->label('Image')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->maxSize(2048)
                    ->directory(StorageConstants::GALLERY_IMAGES)
                    ->required(),

                Hidden::make('user_id')->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')->disk('local')->square(),
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('user.name')->label('Uploaded by')->sortable()->searchable(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                // Optional "My Uploads" filter
                Filter::make('uploaded_by_me')
                    ->label('My Uploads')
                    ->query(fn (Builder $query) => $query->where('user_id', auth()->id()))
                    ->toggle(),

                // Optional "Uploaded By" filter
                SelectFilter::make('user')
                    ->label('Uploaded By')
                    ->relationship('user', 'name')
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGalleryImages::route('/'),
        ];
    }
}
