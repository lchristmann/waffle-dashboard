<?php

namespace App\Filament\Pages;

use App\Models\GalleryImage;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

class Gallery extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected string $view = 'filament.pages.gallery';
    protected static ?int $navigationSort = 30;

    public static function getNavigationLabel(): string
    {
        return __('Gallery');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Image Gallery');
    }

    public function getImages(): Collection
    {
        return GalleryImage::query()->latest('date')->get();
    }
}
