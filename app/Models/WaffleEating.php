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
        'date',
        'count',
        'entered_by_user',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user');
    }
}
