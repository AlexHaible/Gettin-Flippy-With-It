<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_with_genre_matches_exact_genre_names(): void
    {
        $drive = Movie::create(['title' => 'Drive', 'genres' => ['Action', 'Drama'], 'cast' => ['Ryan Gosling']]);
        Movie::create(['title' => 'Hot Fuzz', 'genres' => ['Action Comedy'], 'cast' => ['Simon Pegg']]);

        $this->assertSame([$drive->id], Movie::withGenre('Action')->pluck('id')->all());
        $this->assertSame([], Movie::withGenre('Comedy')->pluck('id')->all());
    }

    public function test_with_actor_matches_exact_actor_names(): void
    {
        $drive = Movie::create(['title' => 'Drive', 'genres' => ['Drama'], 'cast' => ['Ryan Gosling', 'Carey Mulligan']]);
        Movie::create(['title' => 'Barbie', 'genres' => ['Comedy'], 'cast' => ['Margot Robbie']]);

        $this->assertSame([$drive->id], Movie::withActor('Carey Mulligan')->pluck('id')->all());
        $this->assertSame([], Movie::withActor('Ryan')->pluck('id')->all());
    }

    public function test_entity_pages_list_matching_showings(): void
    {
        $user = User::factory()->create();
        $cinema = Cinema::create(['name' => 'Test Cinema']);
        $drive = Movie::create(['title' => 'Drive', 'genres' => ['Drama'], 'cast' => ['Ryan Gosling']]);
        $barbie = Movie::create(['title' => 'Barbie', 'genres' => ['Comedy'], 'cast' => ['Margot Robbie']]);

        foreach ([$drive, $barbie] as $i => $movie) {
            Showing::create([
                'user_id' => $user->id,
                'movie_id' => $movie->id,
                'cinema_id' => $cinema->id,
                'start_time' => now()->subDays($i + 1),
                'google_event_id' => 'event-'.$i,
            ]);
        }

        $this->actingAs($user)->get(route('genre', 'Drama'))
            ->assertOk()
            ->assertViewHas('entityType', 'Genre')
            ->assertViewHas('entityName', 'Drama')
            ->assertSee('Drive')
            ->assertDontSee('Barbie');

        $this->actingAs($user)->get(route('actor', 'Margot Robbie'))
            ->assertOk()
            ->assertViewHas('entityType', 'Actor')
            ->assertSee('Barbie')
            ->assertDontSee('Drive');
    }
}
