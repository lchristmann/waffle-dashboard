<?php

namespace App\Filament\Pages;

use App\Models\GalleryImage;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class Gallery extends Page
{
    protected static ?string $navigationLabel = 'Gallery';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static ?string $title = 'Image Gallery';
    protected string $view = 'filament.pages.gallery';
    protected static ?int $navigationSort = 30;

    public function getImages(): Collection
    {
        return GalleryImage::query()->latest('date')->get();
    }
}
