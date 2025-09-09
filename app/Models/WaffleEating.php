<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // Count distinct days waffles were eaten in a given year
    public static function waffleDaysInYear(int $year): int
    {
        return static::query()
            ->whereYear('date', $year)
            ->distinct('date')
            ->count('date');
    }

    // Count distinct days waffles were eaten in a given month
    public static function waffleDaysInMonth(int $year, int $month): int
    {
        return static::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->distinct('date')
            ->count('date');
    }
}
