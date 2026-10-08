<?php

namespace App\Actions\Watchlist;

use App\Models\User;
use App\Models\WatchlistMovie;
use App\Services\WebhookNotifier;

class HypeWatchlistMovie
{
    public function __construct(private WebhookNotifier $notifier) {}

    /**
     * Add $user to the movie's hype list and announce it once both users want to see it.
     */
    public function __invoke(WatchlistMovie $movie, User $user): void
    {
        $movie->users()->attach($user->id);

        if ($movie->users()->count() >= 2) {
            $this->notifier->notify(
                "🍿 *MUTUAL HYPE ALERT!* Both Alex and Casper want to see *{$movie->title}*! Time to book tickets!"
            );
        }
    }
}
