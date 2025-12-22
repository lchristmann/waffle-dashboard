<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class WaffleEating extends Model
{
    /** @use HasFactory<\Database\Factories\WaffleEatingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'count',
        'entered_by_user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }

    public static function yearTotal(int $year): int
    {
        return static::query()
            ->whereYear('date', $year)
            ->sum('count');
    }

    public static function participatorsInYear(int $year): Collection
    {
        return static::query()
            ->whereYear('date', $year)
            ->distinct('user_id')
            ->pluck('user_id');
    }

    public static function waffleDaysInYear(int $year): Collection
    {
        return static::query()
            ->whereYear('date', $year)
            ->distinct('date')
            ->pluck('date');
    }
}
