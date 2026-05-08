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

        $dayOfWeekStats = Showing::all()
            ->groupBy(fn ($showing) => $showing->start_time->format('l'))
            ->map(fn ($group) => $group->count())
            ->union($defaultStats);

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
        ]);
    }
}
