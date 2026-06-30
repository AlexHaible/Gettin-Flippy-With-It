<?php

namespace Tests\Feature;

use App\Models\WatchlistMovie;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_watchlist_cleanup_command_removes_past_movies_but_keeps_future_and_tba()
    {
        // 1. Create a movie in the past
        $pastMovie = WatchlistMovie::create([
            'tmdb_id' => 101,
            'title' => 'Past Movie',
            'release_date' => today()->subDay(),
        ]);

        // 2. Create a movie released today (should not be deleted, it premieres today)
        $todayMovie = WatchlistMovie::create([
            'tmdb_id' => 102,
            'title' => 'Today Movie',
            'release_date' => today(),
        ]);

        // 3. Create a movie in the future
        $futureMovie = WatchlistMovie::create([
            'tmdb_id' => 103,
            'title' => 'Future Movie',
            'release_date' => today()->addDay(),
        ]);

        // 4. Create a movie with null release date (TBA)
        $tbaMovie = WatchlistMovie::create([
            'tmdb_id' => 104,
            'title' => 'TBA Movie',
            'release_date' => null,
        ]);

        // Verify they all exist initially
        $this->assertDatabaseCount('watchlist_movies', 4);

        // Run command
        $this->artisan('watchlist:cleanup')
            ->expectsOutputToContain('Watchlist cleanup completed. Removed 1 released movies.')
            ->assertExitCode(0);

        // Assert past movie is deleted
        $this->assertDatabaseMissing('watchlist_movies', [
            'id' => $pastMovie->id,
        ]);

        // Assert others still exist
        $this->assertDatabaseHas('watchlist_movies', [
            'id' => $todayMovie->id,
        ]);
        $this->assertDatabaseHas('watchlist_movies', [
            'id' => $futureMovie->id,
        ]);
        $this->assertDatabaseHas('watchlist_movies', [
            'id' => $tbaMovie->id,
        ]);
    }

    public function test_watchlist_cleanup_command_is_scheduled_to_run_weekly_on_mondays()
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        // Find the event that calls the watchlist:cleanup command
        $cleanupEvent = $events->first(function ($event) {
            return str_contains($event->command, 'watchlist:cleanup');
        });

        $this->assertNotNull($cleanupEvent, 'Command watchlist:cleanup was not found in the schedule.');

        // mondays() is equivalent to weeklyOn(1, '00:00') -> cron: '0 0 * * 1'
        $this->assertEquals('0 0 * * 1', $cleanupEvent->expression);
    }
}
