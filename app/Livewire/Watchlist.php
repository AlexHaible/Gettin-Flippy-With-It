<?php

namespace App\Livewire;

use App\Models\WatchlistMovie;
use App\Services\TmdbService;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Watchlist extends Component
{
    public $searchQuery = '';

    public $searchResults = [];

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) < 3) {
            $this->searchResults = [];

            return;
        }

        $tmdbService = app(TmdbService::class);
        $this->searchResults = collect($tmdbService->searchMovies($this->searchQuery))->take(5)->toArray();
    }

    public function addMovie($tmdbId, $title, $posterPath, $releaseDate)
    {
        // Fetch full details to get the collection_id if available
        $details = app(TmdbService::class)->getMovieDetails($tmdbId);
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

        $userId = auth()->id();

        if (! $movie->users()->where('user_id', $userId)->exists()) {
            $movie->users()->attach($userId);

            if ($movie->users()->count() >= 2) {
                $this->dispatchMutualHypeWebhook($movie);
            }
        }

        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function toggleHype($movieId)
    {
        $movie = WatchlistMovie::find($movieId);
        $userId = auth()->id();

        if ($movie->users()->where('user_id', $userId)->exists()) {
            $movie->users()->detach($userId);
        } else {
            $movie->users()->attach($userId);
            if ($movie->users()->count() >= 2) {
                $this->dispatchMutualHypeWebhook($movie);
            }
        }
    }

    protected function dispatchMutualHypeWebhook($movie)
    {
        $discordWebhook = env('DISCORD_WEBHOOK_URL');
        $slackWebhook = env('SLACK_WEBHOOK_URL');

        $message = "🍿 **MUTUAL HYPE ALERT!** Both Alex and Casper want to see **{$movie->title}**! Time to book tickets!";

        if ($discordWebhook) {
            Http::post($discordWebhook, ['content' => $message]);
        }
        if ($slackWebhook) {
            Http::post($slackWebhook, ['text' => $message]);
        }
    }

    public function render()
    {
        return view('livewire.watchlist', [
            'watchlistMovies' => WatchlistMovie::with('users')->orderByRaw('release_date IS NULL')->orderBy('release_date')->get(),
        ])->layout('components.layouts.app');
    }
}
