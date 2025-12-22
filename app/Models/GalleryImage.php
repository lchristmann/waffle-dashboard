<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryImage extends Model
{
    /** @use HasFactory<\Database\Factories\GalleryImageFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'path',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted(): void
    {
        self::deleting(function (GalleryImage $galleryImage) {
            if ($galleryImage->path && Storage::exists($galleryImage->path)) Storage::delete($galleryImage->path);
        });
    }
}
