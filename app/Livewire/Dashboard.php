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

        $currentPayer = User::where('is_current_payer', true)->first();

        return view('livewire.dashboard', compact(
            'totalShowings', 'totalMovies', 'totalSpent', 'totalHours', 'costPerHour', 'averageCost',
            'cinemaDistribution', 'recentShowings', 'payerStats', 'dayOfWeekStats', 'upcomingShowings',
            'heroBackdrop', 'topGenre', 'topActor', 'genreCounts', 'actorCounts',
            'projectedMovies', 'projectedSpend', 'topGenreCount', 'topActorCount',
            'currentPayer'
        ));
    }
}
