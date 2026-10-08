<?php

namespace App\Livewire;

use App\Actions\Watchlist\AddMovieToWatchlist;
use App\Actions\Watchlist\ToggleWatchlistHype;
use App\Models\WatchlistMovie;
use App\Services\TmdbService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Watchlist extends Component
{
    public string $searchQuery = '';

    public array $searchResults = [];

    public function updatedSearchQuery(): void
    {
        if (strlen($this->searchQuery) < 3) {
            $this->searchResults = [];

            return;
        }

        $this->searchResults = collect(app(TmdbService::class)->searchMovies($this->searchQuery))->take(5)->toArray();
    }

    public function addMovie(int $tmdbId, string $title, ?string $posterPath, ?string $releaseDate, AddMovieToWatchlist $addMovieToWatchlist): void
    {
        $addMovieToWatchlist(auth()->user(), $tmdbId, $title, $posterPath, $releaseDate);

        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function toggleHype(int $movieId, ToggleWatchlistHype $toggleWatchlistHype): void
    {
        $toggleWatchlistHype(WatchlistMovie::findOrFail($movieId), auth()->user());
    }

    public function render(): View
    {
        return view('livewire.watchlist', [
            'watchlistMovies' => WatchlistMovie::with('users')
                ->orderByRaw('release_date IS NULL')
                ->orderBy('release_date')
                ->get(),
        ]);
    }
}
