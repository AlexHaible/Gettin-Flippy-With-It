<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchlistMovie extends Model
{
    protected $guarded = [];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'watchlist_movie_user')->withTimestamps();
    }
}
