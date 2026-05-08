<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;

class User extends Authenticatable implements HasPasskeys
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithPasskeys, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'is_current_payer',
    ];

    #[Override]
    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function watchlistMovies()
    {
        return $this->belongsToMany(WatchlistMovie::class, 'watchlist_movie_user')->withTimestamps();
    }

    #[Override]
    public function getPasskeyName(): string
    {
        return $this->username;
    }

    #[Override]
    public function getPasskeyDisplayName(): string
    {
        return $this->username;
    }
}
