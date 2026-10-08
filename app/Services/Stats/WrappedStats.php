<?php

namespace App\Services\Stats;

use App\Models\Showing;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WrappedStats
{
    /**
     * Rating scores mapped to a number so disagreements can be measured.
     */
    private const SCORE_VALUES = ['liked' => 3, 'meh' => 2, 'disliked' => 1];

    /**
     * The year-in-review figures for $year, keyed by the view variable names.
     *
     * @return array<string, mixed>
     */
    public function forYear(int $year): array
    {
        $availableYears = $this->availableYears();

        $showings = Showing::with(['movie', 'cinema', 'popcornPayer', 'user', 'ratings'])
            ->whereYear('start_time', $year)
            ->where('start_time', '<=', now())
            ->get();

        if ($showings->isEmpty()) {
            return [
                'year' => $year,
                'hasData' => false,
                'availableYears' => $availableYears,
            ];
        }

        $totalRuntime = $showings->sum(fn ($s) => $s->movie->runtime ?? 0);
        $topCinema = $this->topCinema($year);

        return [
            'year' => $year,
            'hasData' => true,
            'totalSpent' => $showings->sum('price_total'),
            'totalMovies' => $showings->count(),
            'totalHours' => $totalRuntime > 0 ? floor($totalRuntime / 60) : 0,
            'longestMovie' => $showings->sortByDesc(fn ($s) => $s->movie->runtime ?? 0)->first(),
            'mostExpensive' => $showings->sortByDesc('price_total')->first(),
            'topCinema' => $topCinema ? $topCinema->cinema->name : 'N/A',
            'topCinemaVisits' => $topCinema ? $topCinema->total : 0,
            'alexSnacks' => $showings->where('popcorn_payer_id', User::ALEX_ID)->count(),
            'casperSnacks' => $showings->where('popcorn_payer_id', User::CASPER_ID)->count(),
            'heroBackdrop' => $this->randomBackdrop($showings),
            'availableYears' => $availableYears,
            'biggestDisagreement' => $this->biggestDisagreement($showings),
        ];
    }

    /**
     * @return Collection<int, int>
     */
    private function availableYears(): Collection
    {
        return Showing::whereNotNull('start_time')
            ->get()
            ->pluck('start_time')
            ->map(fn ($d) => $d->year)
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * The most visited cinema in $year, with the visit count in `total`.
     */
    private function topCinema(int $year): ?Showing
    {
        return Showing::whereYear('start_time', $year)
            ->where('start_time', '<=', now())
            ->select('cinema_id', DB::raw('count(*) as total'))
            ->groupBy('cinema_id')
            ->orderByDesc('total')
            ->with('cinema')
            ->first();
    }

    private function randomBackdrop(Collection $showings): ?string
    {
        $moviesWithBackdrop = $showings->filter(fn ($s) => ! empty($s->movie->backdrop_path));

        return $moviesWithBackdrop->isNotEmpty()
            ? 'https://image.tmdb.org/t/p/original'.$moviesWithBackdrop->random()->movie->backdrop_path
            : null;
    }

    /**
     * The first showing with the largest rating gap between Alex and Casper, if they ever disagreed.
     */
    private function biggestDisagreement(Collection $showings): ?Showing
    {
        $biggestDisagreement = null;
        $maxDiff = -1;

        foreach ($showings as $showing) {
            $alexRating = $showing->ratings->firstWhere('user_id', User::ALEX_ID);
            $casperRating = $showing->ratings->firstWhere('user_id', User::CASPER_ID);

            if ($alexRating && $casperRating) {
                $diff = abs((self::SCORE_VALUES[$alexRating->score] ?? 2) - (self::SCORE_VALUES[$casperRating->score] ?? 2));

                if ($diff > $maxDiff && $diff > 0) {
                    $maxDiff = $diff;
                    $biggestDisagreement = $showing;
                }
            }
        }

        return $biggestDisagreement;
    }
}
