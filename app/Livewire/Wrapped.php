<?php

namespace App\Livewire;

use App\Models\Showing;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Wrapped extends Component
{
    #[Url]
    public int $year;

    public function mount($year = null)
    {
        $this->year = $year ?? now()->year;
    }

    public function render()
    {
        $availableYears = Showing::whereNotNull('start_time')
            ->get()
            ->pluck('start_time')
            ->map(fn ($d) => $d->year)
            ->unique()
            ->sortDesc()
            ->values();

        $showings = Showing::with(['movie', 'cinema', 'popcornPayer', 'user', 'ratings'])
            ->whereYear('start_time', $this->year)
            ->where('start_time', '<=', now())
            ->get();

        if ($showings->isEmpty()) {
            return view('livewire.wrapped', [
                'year'           => $this->year,
                'hasData'        => false,
                'availableYears' => $availableYears,
            ]);
        }

        $totalSpent   = $showings->sum('price_total');
        $totalMovies  = $showings->count();
        $totalRuntime = $showings->sum(fn ($s) => $s->movie->runtime ?? 0);
        $totalHours   = $totalRuntime > 0 ? floor($totalRuntime / 60) : 0;

        $longestMovie  = $showings->sortByDesc(fn ($s) => $s->movie->runtime ?? 0)->first();
        $mostExpensive = $showings->sortByDesc('price_total')->first();

        $topCinemaRaw = Showing::whereYear('start_time', $this->year)
            ->where('start_time', '<=', now())
            ->select('cinema_id', DB::raw('count(*) as total'))
            ->groupBy('cinema_id')
            ->orderByDesc('total')
            ->with('cinema')
            ->first();

        $alexSnacks   = $showings->where('popcorn_payer_id', 1)->count();
        $casperSnacks = $showings->where('popcorn_payer_id', 2)->count();

        $moviesWithBackdrop = $showings->filter(fn ($s) => ! empty($s->movie->backdrop_path));
        $heroBackdrop = $moviesWithBackdrop->isNotEmpty()
            ? 'https://image.tmdb.org/t/p/original' . $moviesWithBackdrop->random()->movie->backdrop_path
            : null;

        $biggestDisagreement = null;
        $maxDiff = -1;
        $scoreMap = ['liked' => 3, 'meh' => 2, 'disliked' => 1];

        foreach ($showings as $showing) {
            $alexRating   = $showing->ratings->firstWhere('user_id', 1);
            $casperRating = $showing->ratings->firstWhere('user_id', 2);

            if ($alexRating && $casperRating) {
                $diff = abs(($scoreMap[$alexRating->score] ?? 2) - ($scoreMap[$casperRating->score] ?? 2));
                if ($diff > $maxDiff && $diff > 0) {
                    $maxDiff = $diff;
                    $biggestDisagreement = $showing;
                }
            }
        }

        return view('livewire.wrapped', [
            'year'                => $this->year,
            'hasData'             => true,
            'totalSpent'          => $totalSpent,
            'totalMovies'         => $totalMovies,
            'totalHours'          => $totalHours,
            'longestMovie'        => $longestMovie,
            'mostExpensive'       => $mostExpensive,
            'topCinema'           => $topCinemaRaw ? $topCinemaRaw->cinema->name : 'N/A',
            'topCinemaVisits'     => $topCinemaRaw ? $topCinemaRaw->total : 0,
            'alexSnacks'          => $alexSnacks,
            'casperSnacks'        => $casperSnacks,
            'heroBackdrop'        => $heroBackdrop,
            'availableYears'      => $availableYears,
            'biggestDisagreement' => $biggestDisagreement,
        ]);
    }
}
