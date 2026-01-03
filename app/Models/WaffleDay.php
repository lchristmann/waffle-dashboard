<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaffleDay extends Model
{
    /** @use HasFactory<\Database\Factories\WaffleDayFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'note'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public static function mostRecent(): ?self
    {
        return self::query()
            ->where('date', '<=', now()->toDateString())
            ->orderBy('date', 'desc')
            ->first();
    }

    public static function mostRecentWithinDays(int $days): ?self
    {
        return self::mostRecent()?->date?->gte(now()->subDays($days))
            ? self::mostRecent()
            : null;
    }
}
