<?php

namespace Tests\Feature;

use App\Actions\Watchlist\AddMovieToWatchlist;
use App\Actions\Watchlist\ToggleWatchlistHype;
use App\Livewire\Watchlist;
use App\Models\User;
use App\Models\WatchlistMovie;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class WatchlistActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.discord.webhook_url' => 'https://discord.test/webhook',
            'services.slack.webhook_url' => null,
        ]);

        Http::fake();

        $this->mock(TmdbService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getMovieDetails')->andReturn(['belongs_to_collection' => ['id' => 99]]);
        });
    }

    public function test_adding_a_movie_creates_it_and_only_announces_mutual_hype(): void
    {
        [$alex, $casper] = User::factory()->count(2)->create();
        $add = app(AddMovieToWatchlist::class);

        $movie = $add($alex, 123, 'Dune: Part Three', '/dune.jpg', '2026-12-18');

        $this->assertSame(99, $movie->collection_id);
        $this->assertTrue($movie->users()->whereKey($alex->id)->exists());
        Http::assertNothingSent();

        // Adding the same movie again for the same user changes nothing.
        $add($alex, 123, 'Dune: Part Three', '/dune.jpg', '2026-12-18');
        Http::assertNothingSent();

        $add($casper, 123, 'Dune: Part Three', '/dune.jpg', '2026-12-18');

        $this->assertSame(1, WatchlistMovie::count());
        $this->assertSame(2, $movie->users()->count());
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://discord.test/webhook'
            && str_contains($request['content'], 'Dune: Part Three'));
    }

    public function test_toggling_hype_attaches_detaches_and_announces_mutual_hype(): void
    {
        [$alex, $casper] = User::factory()->count(2)->create();
        $movie = WatchlistMovie::create(['tmdb_id' => 456, 'title' => 'Paddington 4']);
        $toggle = app(ToggleWatchlistHype::class);

        $this->assertTrue($toggle($movie, $alex));
        Http::assertNothingSent();

        $this->assertTrue($toggle($movie, $casper));
        Http::assertSentCount(1);

        $this->assertFalse($toggle($movie, $casper));
        $this->assertSame([$alex->id], $movie->users()->pluck('users.id')->all());
        Http::assertSentCount(1);
    }

    public function test_the_watchlist_component_adds_a_movie_and_clears_the_search(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Watchlist::class)
            ->set('searchResults', [['id' => 789, 'title' => 'Knives Out 3']])
            ->call('addMovie', 789, 'Knives Out 3', null, null)
            ->assertSet('searchQuery', '')
            ->assertSet('searchResults', []);

        $movie = WatchlistMovie::where('tmdb_id', 789)->firstOrFail();
        $this->assertTrue($movie->users()->whereKey($user->id)->exists());
    }
}
