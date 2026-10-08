<?php

namespace App\Actions\Watchlist;

use App\Models\User;
use App\Models\WatchlistMovie;

class ToggleWatchlistHype
{
    public function __construct(private HypeWatchlistMovie $hype) {}

    /**
     * Add or remove $user from the movie's hype list.
     *
     * Returns true when the user is hyped after the toggle.
     */
    public function __invoke(WatchlistMovie $movie, User $user): bool
    {
        if ($movie->users()->where('user_id', $user->id)->exists()) {
            $movie->users()->detach($user->id);

            return false;
        }

        ($this->hype)($movie, $user);

        return true;
    }
}
