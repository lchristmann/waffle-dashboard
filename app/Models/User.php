<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'avatar'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // Waffles this user ate
    public function waffleEatings(): HasMany
    {
        return $this->hasMany(WaffleEating::class, 'user_id');
    }

    // Waffles this user entered (for themselves or others)
    public function enteredWaffleEatings(): HasMany
    {
        return $this->hasMany(WaffleEating::class, 'entered_by_user_id');
    }

    // Waffles this user ate in a given year
    public function wafflesEatenInYear(int $year): int
    {
        return $this->waffleEatings()
            ->whereYear('date', $year)
            ->sum('count');
    }

    // Total waffles eaten by the user in a given month
    public function wafflesEatenInMonth(int $year, int $month): int
    {
        return $this->waffleEatings()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('count');
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar
            ? route('user.avatar', $this)
            : null;
    }

    protected static function booted(): void
    {
        static::updating(function (User $user) {
            if ($user->isDirty('avatar') && $user->getOriginal('avatar')) {
                Storage::delete($user->getOriginal('avatar'));
            }
        });

        static::deleting(function (User $user) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }
        });
    }
}
