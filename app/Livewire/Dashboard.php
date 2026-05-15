<?php

namespace App\Livewire;

use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use App\Models\WatchlistMovie;
use App\Services\TmdbService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $totalShowings = Showing::count();
        $totalMovies = Movie::count();
        $totalSpent = Showing::sum('price_total');

        $cinemaDistribution = Showing::select('cinema_id', DB::raw('count(*) as total'))
            ->with('cinema')
            ->groupBy('cinema_id')
            ->orderByDesc('total')
            ->get();

        $recentShowings = Showing::with(['movie', 'cinema'])
            ->orderByDesc('start_time')
            ->take(5)
            ->get();

        $totalRuntimeMinutes = Showing::join('movies', 'showings.movie_id', '=', 'movies.id')
            ->sum('movies.runtime');

        $totalHours = $totalRuntimeMinutes > 0 ? $totalRuntimeMinutes / 60 : 0;
        $costPerHour = $totalHours > 0 ? $totalSpent / $totalHours : 0;
        $averageCost = $totalShowings > 0 ? $totalSpent / $totalShowings : 0;

        $splitAmount = $totalSpent / 2;
        $splitTickets = $totalShowings / 2;

        $alex = User::find(1);
        $casper = User::find(2);

        if (! $casper) {
            $casper = new User(['username' => 'Casper']);
            $casper->id = 2;
        }

        $payerStats = collect([
            (object) ['user' => $alex, 'total_spent' => $splitAmount, 'tickets_bought' => $splitTickets],
            (object) ['user' => $casper, 'total_spent' => $splitAmount, 'tickets_bought' => $splitTickets],
        ]);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $defaultStats = array_fill_keys($days, 0);

        $dayOfWeekStats = Showing::where('start_time', '<=', now())
            ->get()
            ->groupBy(fn ($s) => $s->start_time->format('l'))
            ->map(fn ($g) => $g->count())
            ->union($defaultStats);

        $upcomingShowings = Showing::with(['movie', 'cinema'])
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();

        $heroBackdrop = null;
        if ($upcomingShowings->isNotEmpty() && $upcomingShowings->first()->movie->backdrop_path) {
            $heroBackdrop = 'https://image.tmdb.org/t/p/original' . $upcomingShowings->first()->movie->backdrop_path;
        }

        $allMovies = Movie::withCount('showings')->get();
        $genreCounts = [];
        $actorCounts = [];

        foreach ($allMovies as $movie) {
            if ($movie->showings_count == 0) continue;
            foreach (json_decode($movie->genres, true) ?? [] as $genre) {
                $genreCounts[$genre] = ($genreCounts[$genre] ?? 0) + $movie->showings_count;
            }
            foreach (json_decode($movie->cast, true) ?? [] as $actor) {
                $actorCounts[$actor] = ($actorCounts[$actor] ?? 0) + $movie->showings_count;
            }
        }

        arsort($genreCounts);
        arsort($actorCounts);

        $topGenre      = key($genreCounts) ?? 'N/A';
        $topActor      = key($actorCounts) ?? 'N/A';
        $topGenreCount = $genreCounts[$topGenre] ?? 0;
        $topActorCount = $actorCounts[$topActor] ?? 0;

        $daysPassed = now()->dayOfYear;
        $totalDays = now()->isLeapYear() ? 366 : 365;
        $paceMultiplier = $daysPassed > 0 ? $totalDays / $daysPassed : 1;

        $currentYearShowings = Showing::whereYear('start_time', now()->year)->get();
        $projectedMovies = round($currentYearShowings->count() * $paceMultiplier);
        $projectedSpend = round($currentYearShowings->sum('price_total') * $paceMultiplier);

        $tmdbService = app(TmdbService::class);
        $nowPlaying = collect($tmdbService->getNowPlaying());
        $upcoming = collect($tmdbService->getUpcoming());

        $seenTmdbIds = Movie::whereNotNull('tmdb_id')->pluck('tmdb_id')->flip();
        $pool = $nowPlaying->merge($upcoming)->unique('id')
            ->reject(fn ($m) => $seenTmdbIds->has($m['id']))->shuffle();

        $genreMap = [
            'Action' => 28, 'Adventure' => 12, 'Animation' => 16, 'Comedy' => 35, 'Crime' => 80,
            'Documentary' => 99, 'Drama' => 18, 'Family' => 10751, 'Fantasy' => 14, 'History' => 36,
            'Horror' => 27, 'Music' => 10402, 'Mystery' => 9648, 'Romance' => 10749,
            'Science Fiction' => 878, 'TV Movie' => 10770, 'Thriller' => 53, 'War' => 10752, 'Western' => 37,
        ];

        $topGenreId = $genreMap[$topGenre] ?? null;
        if ($topGenreId) {
            $recommendations = $pool->filter(fn ($m) => in_array($topGenreId, $m['genre_ids'] ?? []))->take(4);
            if ($recommendations->count() < 4) {
                $recommendations = $recommendations->merge(
                    $pool->whereNotIn('id', $recommendations->pluck('id'))->take(4 - $recommendations->count())
                );
            }
        } else {
            $recommendations = $pool->take(4);
        }

        $rewatchRadar = null;
        foreach (WatchlistMovie::whereNotNull('collection_id')->get() as $watchlistMovie) {
            $collection = $tmdbService->getCollection($watchlistMovie->collection_id);
            if (! $collection) continue;

            $parts = collect($collection['parts'] ?? [])->sortBy('release_date')->values();
            $targetIndex = $parts->search(fn ($p) => $p['id'] === $watchlistMovie->tmdb_id);

            if ($targetIndex !== false && $targetIndex > 0) {
                $prev = $parts[$targetIndex - 1];
                $rewatchRadar = [
                    'watchlist_title' => $watchlistMovie->title,
                    'prev_title'      => $prev['title'],
                    'prev_poster'     => $prev['poster_path'] ?? null,
                    'prev_year'       => isset($prev['release_date']) ? substr($prev['release_date'], 0, 4) : null,
                    'collection_name' => $collection['name'],
                ];
                break;
            }
        }

        return view('livewire.dashboard', compact(
            'totalShowings', 'totalMovies', 'totalSpent', 'totalHours', 'costPerHour', 'averageCost',
            'cinemaDistribution', 'recentShowings', 'payerStats', 'dayOfWeekStats', 'upcomingShowings',
            'heroBackdrop', 'topGenre', 'topActor', 'genreCounts', 'actorCounts',
            'projectedMovies', 'projectedSpend', 'recommendations', 'rewatchRadar',
            'topGenreCount', 'topActorCount'
        ));
    }
}
