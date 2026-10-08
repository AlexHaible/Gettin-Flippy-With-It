<?php

use App\Models\User;
use App\Services\TmdbService;
use Tests\Support\BrowserFixtures;
use Tests\Support\FakeTmdbService;

it('searches tmdb and adds a result to the watchlist', function () {
    [$alex] = BrowserFixtures::users();

    // The browser plugin serves the app from this process, so container bindings apply.
    $this->app->instance(TmdbService::class, new FakeTmdbService([
        ['id' => 9001, 'title' => 'Nebula Run', 'poster_path' => null, 'release_date' => '2027-03-12'],
        ['id' => 9002, 'title' => 'Nebula Run II', 'poster_path' => null, 'release_date' => '2028-11-03'],
    ]));

    $this->actingAs($alex);

    $page = visitLocal('/watchlist')
        ->assertSee('Anticipated Watchlist')
        ->assertDontSee('Nebula Run');

    $page->fill('input[placeholder="Search for upcoming movies..."]', 'Nebula');

    $page->assertSee('Nebula Run II')
        ->assertSee('2027')
        ->assertSee('2028');

    $page->click('Nebula Run');

    $page->assertSee('Mar 12, 2027')
        ->assertDontSee('Nebula Run II')
        ->assertValue('input[placeholder="Search for upcoming movies..."]', '')
        ->assertNoJavaScriptErrors();

    $this->assertDatabaseHas('watchlist_movies', ['tmdb_id' => 9001, 'title' => 'Nebula Run']);
    $this->assertDatabaseMissing('watchlist_movies', ['tmdb_id' => 9002]);
    $this->assertDatabaseHas('watchlist_movie_user', ['user_id' => User::ALEX_ID]);
});
