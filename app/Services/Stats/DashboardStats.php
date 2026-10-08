<?php

namespace App\Services\Stats;

use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use App\Services\MovieStats;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardStats
{
    public function __construct(private MovieStats $movieStats) {}

    /**
     * All dashboard figures, keyed by the view variable names.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $totalShowings = Showing::count();
        $totalSpent = Showing::sum('price_total');

        $totalRuntimeMinutes = Showing::join('movies', 'showings.movie_id', '=', 'movies.id')
            ->sum('movies.runtime');

        $totalHours = $totalRuntimeMinutes > 0 ? $totalRuntimeMinutes / 60 : 0;

        $upcomingShowings = Showing::with(['movie', 'cinema'])
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();

        return [
            'totalShowings' => $totalShowings,
            'totalMovies' => Movie::count(),
            'totalSpent' => $totalSpent,
            'totalHours' => $totalHours,
            'costPerHour' => $totalHours > 0 ? $totalSpent / $totalHours : 0,
            'averageCost' => $totalShowings > 0 ? $totalSpent / $totalShowings : 0,
            'cinemaDistribution' => $this->cinemaDistribution(),
            'recentShowings' => Showing::with(['movie', 'cinema'])->orderByDesc('start_time')->take(5)->get(),
            'payerStats' => $this->payerStats($totalSpent, $totalShowings),
            'dayOfWeekStats' => $this->dayOfWeekStats(),
            'upcomingShowings' => $upcomingShowings,
            'heroBackdrop' => $this->heroBackdrop($upcomingShowings),
            ...$this->favourites(),
            ...$this->projections(),
            'currentPayer' => User::where('is_current_payer', true)->first(),
        ];
    }

    private function cinemaDistribution(): Collection
    {
        return Showing::select('cinema_id', DB::raw('count(*) as total'))
            ->with('cinema')
            ->groupBy('cinema_id')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Tickets and spend are split evenly between Alex and Casper.
     */
    private function payerStats(mixed $totalSpent, int $totalShowings): Collection
    {
        $splitAmount = $totalSpent / 2;
        $splitTickets = $totalShowings / 2;

        $alex = User::find(User::ALEX_ID);
        $casper = User::find(User::CASPER_ID);

        if (! $casper) {
            $casper = new User(['username' => 'Casper']);
            $casper->id = User::CASPER_ID;
        }

        return collect([
            (object) ['user' => $alex, 'total_spent' => $splitAmount, 'tickets_bought' => $splitTickets],
            (object) ['user' => $casper, 'total_spent' => $splitAmount, 'tickets_bought' => $splitTickets],
        ]);
    }

    /**
     * Past showings per weekday name; weekdays without showings are appended with 0.
     */
    private function dayOfWeekStats(): Collection
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return Showing::where('start_time', '<=', now())
            ->get()
            ->groupBy(fn ($s) => $s->start_time->format('l'))
            ->map(fn ($g) => $g->count())
            ->union(array_fill_keys($days, 0));
    }

    private function heroBackdrop(Collection $upcomingShowings): ?string
    {
        if ($upcomingShowings->isNotEmpty() && $upcomingShowings->first()->movie->backdrop_path) {
            return 'https://image.tmdb.org/t/p/original'.$upcomingShowings->first()->movie->backdrop_path;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function favourites(): array
    {
        $genreCounts = $this->movieStats->genreCounts();
        $actorCounts = $this->movieStats->actorCounts();

        $topGenre = array_key_first($genreCounts) ?? 'N/A';
        $topActor = array_key_first($actorCounts) ?? 'N/A';

        return [
            'topGenre' => $topGenre,
            'topActor' => $topActor,
            'genreCounts' => $genreCounts,
            'actorCounts' => $actorCounts,
            'topGenreCount' => $genreCounts[$topGenre] ?? 0,
            'topActorCount' => $actorCounts[$topActor] ?? 0,
        ];
    }

    /**
     * Extrapolate this year's showings and spend to a full year at the current pace.
     *
     * @return array{projectedMovies: float, projectedSpend: float}
     */
    private function projections(): array
    {
        $daysPassed = now()->dayOfYear;
        $totalDays = now()->isLeapYear() ? 366 : 365;
        $paceMultiplier = $daysPassed > 0 ? $totalDays / $daysPassed : 1;

        $currentYearShowings = Showing::whereYear('start_time', now()->year)->get();

        return [
            'projectedMovies' => round($currentYearShowings->count() * $paceMultiplier),
            'projectedSpend' => round($currentYearShowings->sum('price_total') * $paceMultiplier),
        ];
    }
}
