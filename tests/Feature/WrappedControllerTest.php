<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WrappedControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_wrapped_page_loads_with_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $movie = Movie::create(['runtime' => 120, 'title' => 'Test Movie']);
        $cinema = Cinema::create(['name' => 'Test Cinema']);

        Showing::create([
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'start_time' => now()->subDays(5),
            'price_total' => 150,
            'google_event_id' => 'test-id-123',
        ]);

        $response = $this->get(route('wrapped', ['year' => now()->year]));

        $response->assertStatus(200);
        $response->assertSee('Wrapped '.now()->year);
        $response->assertSee('Test Movie');
        $response->assertSee('150 kr.');
        $response->assertSee('Test Cinema');
    }

    public function test_wrapped_page_shows_no_data_message_when_empty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('wrapped', ['year' => 2020]));

        $response->assertStatus(200);
        $response->assertSee('Wrapped 2020');
        $response->assertSee('No movies recorded in 2020.');
    }
}
