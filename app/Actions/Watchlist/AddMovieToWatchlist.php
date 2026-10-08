<?php

namespace App\Actions\Watchlist;

use App\Models\User;
use App\Models\WatchlistMovie;
use App\Services\TmdbService;

class AddMovieToWatchlist
{
    public function __construct(
        private TmdbService $tmdb,
        private HypeWatchlistMovie $hype,
    ) {}

    public function __invoke(User $user, int $tmdbId, string $title, ?string $posterPath, ?string $releaseDate): WatchlistMovie
    {
        // Fetch full details to get the collection_id if available
        $details = $this->tmdb->getMovieDetails($tmdbId);
        $collectionId = $details['belongs_to_collection']['id'] ?? null;

        $movie = WatchlistMovie::firstOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'title' => $title,
                'poster_path' => $posterPath,
                'release_date' => $releaseDate,
                'collection_id' => $collectionId,
            ]
        );

        // Update collection_id if missing on an existing record
        if ($movie->collection_id === null && $collectionId) {
            $movie->update(['collection_id' => $collectionId]);
        }

        if (! $movie->users()->where('user_id', $user->id)->exists()) {
            ($this->hype)($movie, $user);
        }

        return $movie;
    }
}
