<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class RemoteWaffleEating extends Model
{
    /** @use HasFactory<\Database\Factories\RemoteWaffleEatingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'count',
        'image',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return ! is_null($this->approved_by);
    }

    public static function yearTotal(int $year): int
    {
        return static::query()
            ->whereNotNull('approved_by')
            ->whereYear('date', $year)
            ->sum('count');
    }

    public static function participatorsInYear(int $year): Collection
    {
        return static::query()
            ->whereNotNull('approved_by')
            ->whereYear('date', $year)
            ->distinct('user_id')
            ->pluck('user_id');
    }

    public static function waffleDaysInYear(int $year): Collection
    {
        return static::query()
            ->whereNotNull('approved_by')
            ->whereYear('date', $year)
            ->distinct('date')
            ->pluck('date');
    }

    protected static function booted(): void
    {
        self::deleting(function (RemoteWaffleEating $waffleEating) {
            if ($waffleEating->image && Storage::exists($waffleEating->image)) Storage::delete($waffleEating->image);
        });
    }
}
