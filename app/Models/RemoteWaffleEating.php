<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected static function booted(): void
    {
        self::deleting(function (RemoteWaffleEating $waffleEating) {
            if ($waffleEating->image && Storage::exists($waffleEating->image)) Storage::delete($waffleEating->image);
        });
    }
}
