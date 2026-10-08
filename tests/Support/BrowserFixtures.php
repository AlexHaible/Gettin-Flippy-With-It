<?php

namespace Tests\Support;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Seeds the two app users and showings for browser tests.
 */
class BrowserFixtures
{
    /**
     * @return array{0: User, 1: User}
     */
    public static function users(bool $alexIsPayer = true): array
    {
        $alex = User::forceCreate([
            'id' => User::ALEX_ID,
            'username' => 'Alex',
            'is_current_payer' => $alexIsPayer,
        ]);

        $casper = User::forceCreate([
            'id' => User::CASPER_ID,
            'username' => 'Casper',
            'is_current_payer' => ! $alexIsPayer,
        ]);

        return [$alex, $casper];
    }

    public static function showing(User $user, string $title, string $cinemaName, CarbonInterface $startTime): Showing
    {
        $cinema = Cinema::firstOrCreate(['name' => $cinemaName]);

        $movie = Movie::create([
            'title' => $title,
            'runtime' => 120,
            'genres' => ['Science Fiction'],
            'cast' => ['Jane Lead'],
            'director' => 'Ada Director',
        ]);

        return Showing::create([
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'start_time' => $startTime,
            'price_total' => 150,
            'google_event_id' => 'browser-'.$movie->id,
        ]);
    }
}
