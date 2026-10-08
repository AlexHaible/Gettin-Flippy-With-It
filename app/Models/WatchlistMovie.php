<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WatchlistMovie extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'watchlist_movie_user')->withTimestamps();
    }
}
