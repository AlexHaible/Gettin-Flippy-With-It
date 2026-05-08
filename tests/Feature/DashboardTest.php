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
            'start_time' => now(),
            'price_total' => 150,
            'google_event_id' => 'test-event-1',
        ]);

        // Act
        $response = $this->get(route('dashboard'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Total Movies');
        $response->assertSee('1'); // Total count
        $response->assertSee('150 kr.'); // Total spend formatted

        $response->assertSee('Total Time');
        $response->assertSee('2,0'); // Value
        $response->assertSee('hrs'); // Unit

        $response->assertSee('Avg. Cost / Movie');
        $response->assertSee('150 kr.'); // 150 / 1

        $response->assertSee('Cost / Hour');
        $response->assertSee('75'); // Value
        $response->assertSee('kr.'); // Unit (might fail if space is weird)

        $response->assertSee('Payer Breakdown');
        $response->assertSee('Weekly Habits');
        $response->assertSee('Vue Test'); // Cinema name
        $response->assertSee('Test Movie'); // Movie title
    }
}
