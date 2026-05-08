<?php

namespace App\Http\Controllers;

use App\Models\Showing;
use Illuminate\Support\Facades\DB;

class WrappedController extends Controller
{
    public function show($year = null)
    {
        $year = $year ?? now()->year;

        $availableYears = Showing::whereNotNull('start_time')
            ->get()
            ->pluck('start_time')
            ->map(fn($date) => $date->year)
            ->unique()
            ->sortDesc()
            ->values();

        $showings = Showing::with(['movie', 'cinema', 'popcornPayer', 'user', 'ratings'])
            ->whereYear('start_time', $year)
            ->where('start_time', '<=', now())
            ->get();

        if ($showings->isEmpty()) {
            return view('wrapped', ['year' => $year, 'hasData' => false, 'availableYears' => $availableYears]);
        }

        $totalSpent = $showings->sum('price_total');
        $totalMovies = $showings->count();
        $totalRuntime = $showings->sum(fn($s) => $s->movie->runtime ?? 0);
        $totalHours = $totalRuntime > 0 ? floor($totalRuntime / 60) : 0;
        
        $longestMovie = $showings->sortByDesc(fn($s) => $s->movie->runtime ?? 0)->first();
        $mostExpensive = $showings->sortByDesc('price_total')->first();
        
        $topCinemaRaw = Showing::whereYear('start_time', $year)
            ->where('start_time', '<=', now())
            ->select('cinema_id', DB::raw('count(*) as total'))
            ->groupBy('cinema_id')
            ->orderByDesc('total')
            ->with('cinema')
            ->first();

        $alexSnacks = $showings->where('popcorn_payer_id', 1)->count();
        $casperSnacks = $showings->where('popcorn_payer_id', 2)->count();

        // Get a random backdrop from the year to serve as the hero background
        $moviesWithBackdrop = $showings->filter(fn($s) => !empty($s->movie->backdrop_path));
        $heroBackdrop = $moviesWithBackdrop->isNotEmpty() 
            ? 'https://image.tmdb.org/t/p/original' . $moviesWithBackdrop->random()->movie->backdrop_path 
            : null;

        $biggestDisagreement = null;
        $maxDiff = -1;
        $scoreMap = ['liked' => 3, 'meh' => 2, 'disliked' => 1];

        foreach ($showings as $showing) {
            $alexRating = $showing->ratings->firstWhere('user_id', 1);
            $casperRating = $showing->ratings->firstWhere('user_id', 2);

            if ($alexRating && $casperRating) {
                $alexScore = $scoreMap[$alexRating->score] ?? 2;
                $casperScore = $scoreMap[$casperRating->score] ?? 2;
                
                $diff = abs($alexScore - $casperScore);
                if ($diff > $maxDiff && $diff > 0) {
                    $maxDiff = $diff;
                    $biggestDisagreement = $showing;
                }
            }
        }

        return view('wrapped', [
            'year' => $year,
            'hasData' => true,
            'totalSpent' => $totalSpent,
            'totalMovies' => $totalMovies,
            'totalHours' => $totalHours,
            'longestMovie' => $longestMovie,
            'mostExpensive' => $mostExpensive,
            'topCinema' => $topCinemaRaw ? $topCinemaRaw->cinema->name : 'N/A',
            'topCinemaVisits' => $topCinemaRaw ? $topCinemaRaw->total : 0,
            'alexSnacks' => $alexSnacks,
            'casperSnacks' => $casperSnacks,
            'heroBackdrop' => $heroBackdrop,
            'availableYears' => $availableYears,
            'biggestDisagreement' => $biggestDisagreement,
        ]);
    }
}
