<?php

namespace Tests\Feature;

use App\Actions\RateShowing;
use App\Livewire\ShowingsList;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Rating;
use App\Models\Showing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class RateShowingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_and_then_updates_a_rating(): void
    {
        $user = User::factory()->create();
        $showing = $this->showing($user);

        app(RateShowing::class)($showing, $user, 'liked');

        $this->assertDatabaseHas('ratings', ['showing_id' => $showing->id, 'user_id' => $user->id, 'score' => 'liked']);

        app(RateShowing::class)($showing, $user, 'disliked');

        $this->assertSame(1, Rating::count());
        $this->assertDatabaseHas('ratings', ['showing_id' => $showing->id, 'user_id' => $user->id, 'score' => 'disliked']);
    }

    public function test_it_rejects_unknown_scores(): void
    {
        $user = User::factory()->create();
        $showing = $this->showing($user);

        $this->expectException(ValidationException::class);

        try {
            app(RateShowing::class)($showing, $user, 'amazing');
        } finally {
            $this->assertSame(0, Rating::count());
        }
    }

    public function test_the_showings_list_component_rates_the_selected_showing(): void
    {
        $user = User::factory()->create();
        $showing = $this->showing($user);

        Livewire::actingAs($user)
            ->test(ShowingsList::class)
            ->call('openModal', $showing->id)
            ->call('rateShowing', 'meh')
            ->assertHasNoErrors()
            ->call('rateShowing', 'amazing')
            ->assertHasErrors('score');

        $this->assertDatabaseHas('ratings', ['showing_id' => $showing->id, 'user_id' => $user->id, 'score' => 'meh']);
    }

    private function showing(User $user): Showing
    {
        return Showing::create([
            'user_id' => $user->id,
            'movie_id' => Movie::create(['title' => 'Test Movie'])->id,
            'cinema_id' => Cinema::create(['name' => 'Test Cinema'])->id,
            'start_time' => now()->subDay(),
            'google_event_id' => 'event-1',
        ]);
    }
}
