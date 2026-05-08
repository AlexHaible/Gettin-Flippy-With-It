<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_and_displays_stats()
    {
        // Arrange
        $user = User::factory()->create();
        $cinema = Cinema::create(['name' => 'Vue Test']);
        // Ensure runtime is set
        $movie = Movie::create(['title' => 'Test Movie', 'runtime' => 120]); // 2 hours

        Showing::create([
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'start_time' => now()->subDays(1),
            'price_total' => 150,
            'google_event_id' => 'test-event-1',
        ]);

        Showing::create([
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'start_time' => now()->addDays(2),
            'price_total' => 0,
            'google_event_id' => 'test-event-2',
            'popcorn_payer_id' => $user->id,
        ]);

        // Act
        $response = $this->get(route('dashboard'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Total Movies');
        $response->assertSee('2'); // Total count
        $response->assertSee('150 kr.'); // Total spend formatted

        $response->assertSee('Total Time');
        $response->assertSee('4,0'); // Value
        $response->assertSee('hrs'); // Unit

        $response->assertSee('Avg. Cost / Movie');
        $response->assertSee('75'); // 150 / 2

        $response->assertSee('Cost / Hour');
        $response->assertSee('38'); // 150 / 4 rounded

        $response->assertSee('Payer Breakdown');
        $response->assertSee('Weekly Habits');
        $response->assertSee('Vue Test'); // Cinema name
        $response->assertSee('Test Movie'); // Movie title
        $response->assertSee('Upcoming Movie Night');
    }
}
