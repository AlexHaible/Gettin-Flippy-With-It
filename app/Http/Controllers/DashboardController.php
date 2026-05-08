<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Movies Watched (count of showings)
        $totalShowings = Showing::count();

        // 2. Total Unique Movies
        $totalMovies = Movie::count(); // Or Showing::distinct('movie_id')->count() if we only care about watched ones

        // 3. Total Money Spent
        $totalSpent = Showing::sum('price_total');

        // 4. Cinema Distribution
        $cinemaDistribution = Showing::select('cinema_id', DB::raw('count(*) as total'))
            ->with('cinema')
            ->groupBy('cinema_id')
            ->orderByDesc('total')
            ->get();

        // 5. Recent Showings
        $recentShowings = Showing::with(['movie', 'cinema'])
            ->orderByDesc('start_time')
            ->take(5)
            ->get();

        // 6. Total Runtime & Cost per Hour
        $totalRuntimeMinutes = Showing::join('movies', 'showings.movie_id', '=', 'movies.id')
            ->sum('movies.runtime');

        $totalHours = $totalRuntimeMinutes > 0 ? $totalRuntimeMinutes / 60 : 0;
        $costPerHour = $totalHours > 0 ? $totalSpent / $totalHours : 0;

        // 7. Average Cost
        $averageCost = $totalShowings > 0 ? $totalSpent / $totalShowings : 0;

        // 8. Payer Breakdown (Manual 50/50 Split)
        $splitAmount = $totalSpent / 2;
        $splitTickets = $totalShowings / 2;

        $alex = User::find(1);
        $casper = User::find(2);

        if (! $casper) {
            $casper = new User(['username' => 'Casper']);
            $casper->id = 2; // Mock ID for view logic if needed
        }

        $payerStats = collect([
            (object) [
                'user' => $alex,
                'total_spent' => $splitAmount,
                'tickets_bought' => $splitTickets,
            ],
            (object) [
                'user' => $casper,
                'total_spent' => $splitAmount,
                'tickets_bought' => $splitTickets,
            ],
        ]);

        // 9. Day of Week Distribution
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $defaultStats = array_fill_keys($days, 0);

        $dayOfWeekStats = Showing::where('start_time', '<=', now())
            ->get()
            ->groupBy(fn ($showing) => $showing->start_time->format('l'))
            ->map(fn ($group) => $group->count())
            ->union($defaultStats);

        // 10. Upcoming Showings
        $upcomingShowings = Showing::with(['movie', 'cinema'])
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();

        // 11. Dynamic Backdrop
        $heroBackdrop = null;
        if ($upcomingShowings->isNotEmpty() && $upcomingShowings->first()->movie->backdrop_path) {
            $heroBackdrop = 'https://image.tmdb.org/t/p/original' . $upcomingShowings->first()->movie->backdrop_path;
        } elseif ($recentShowings->isNotEmpty() && $recentShowings->first()->movie->backdrop_path) {
            $heroBackdrop = 'https://image.tmdb.org/t/p/original' . $recentShowings->first()->movie->backdrop_path;
        }

        // 12. Most Watched Actor & Top Genre
        $allMovies = Movie::withCount('showings')->get();
        
        $genreCounts = [];
        $actorCounts = [];

        foreach ($allMovies as $movie) {
            if ($movie->showings_count == 0) continue;

            $genres = json_decode($movie->genres, true) ?? [];
            foreach ($genres as $genre) {
                $genreCounts[$genre] = ($genreCounts[$genre] ?? 0) + $movie->showings_count;
            }

            $cast = json_decode($movie->cast, true) ?? [];
            foreach ($cast as $actor) {
                $actorCounts[$actor] = ($actorCounts[$actor] ?? 0) + $movie->showings_count;
            }
        }

        arsort($genreCounts);
        arsort($actorCounts);

        $topGenre = key($genreCounts) ?? 'N/A';
        $topActor = key($actorCounts) ?? 'N/A';

        // 13. The Pace Projections
        $daysPassed = now()->dayOfYear;
        $totalDays = now()->isLeapYear() ? 366 : 365;
        $paceMultiplier = $daysPassed > 0 ? $totalDays / $daysPassed : 1;
        
        $currentYearShowings = Showing::whereYear('start_time', now()->year)->get();
        $currentYearMoviesCount = $currentYearShowings->count();
        $currentYearSpendCount = $currentYearShowings->sum('price_total');

        $projectedMovies = round($currentYearMoviesCount * $paceMultiplier);
        $projectedSpend = round($currentYearSpendCount * $paceMultiplier);

        // 14. Recommendations (You Should See)
        $tmdbService = app(\App\Services\TmdbService::class);
        $nowPlaying = collect($tmdbService->getNowPlaying());
        $upcoming = collect($tmdbService->getUpcoming());
        $pool = $nowPlaying->merge($upcoming)->unique('id')->shuffle();

        $genreMap = [
            'Action' => 28, 'Adventure' => 12, 'Animation' => 16, 'Comedy' => 35, 'Crime' => 80,
            'Documentary' => 99, 'Drama' => 18, 'Family' => 10751, 'Fantasy' => 14, 'History' => 36,
            'Horror' => 27, 'Music' => 10402, 'Mystery' => 9648, 'Romance' => 10749,
            'Science Fiction' => 878, 'TV Movie' => 10770, 'Thriller' => 53, 'War' => 10752, 'Western' => 37
        ];
        
        $topGenreId = $genreMap[$topGenre] ?? null;

        if ($topGenreId) {
            $recommendations = $pool->filter(function($movie) use ($topGenreId) {
                return in_array($topGenreId, $movie['genre_ids'] ?? []);
            })->take(4);
            
            // Pad with random if not enough matches
            if ($recommendations->count() < 4) {
                $recommendations = $recommendations->merge($pool->whereNotIn('id', $recommendations->pluck('id'))->take(4 - $recommendations->count()));
            }
        } else {
            $recommendations = $pool->take(4);
        }

        return view('dashboard', [
            'totalShowings' => $totalShowings,
            'totalMovies' => $totalMovies,
            'totalSpent' => $totalSpent,
            'totalHours' => $totalHours,
            'costPerHour' => $costPerHour,
            'averageCost' => $averageCost,
            'cinemaDistribution' => $cinemaDistribution,
            'recentShowings' => $recentShowings,
            'payerStats' => $payerStats,
            'dayOfWeekStats' => $dayOfWeekStats,
            'upcomingShowings' => $upcomingShowings,
            'heroBackdrop' => $heroBackdrop,
            'topGenre' => $topGenre,
            'topActor' => $topActor,
            'topGenreCount' => $genreCounts[$topGenre] ?? 0,
            'topActorCount' => $actorCounts[$topActor] ?? 0,
            'projectedMovies' => $projectedMovies,
            'projectedSpend' => $projectedSpend,
            'recommendations' => $recommendations,
        ]);
    }
}
