<?php

use App\Models\BingoGoal;
use App\Models\User;
use Tests\Support\BrowserFixtures;

it('opens a showing modal and records a rating', function () {
    [$alex] = BrowserFixtures::users();
    $showing = BrowserFixtures::showing($alex, 'Orbital Drift', 'Grand Palais Cinema', now()->subDays(3));

    $this->actingAs($alex);

    $page = visitLocal('/showings')
        ->assertSee('Watched Movies')
        ->assertSee('Orbital Drift')
        ->assertSee('End of list')
        ->assertDontSee('Admit Two');

    $page->click('Orbital Drift');

    $page->assertSee('Admit Two')
        ->assertSee('Unrated');

    $page->click('Liked');

    $page->assertSeeIn('#ticket-card', 'liked')
        ->assertNoJavaScriptErrors();

    $this->assertDatabaseHas('ratings', [
        'showing_id' => $showing->id,
        'user_id' => User::ALEX_ID,
        'score' => 'liked',
    ]);
});

it('switches the showings list to the grid view', function () {
    [$alex] = BrowserFixtures::users();
    BrowserFixtures::showing($alex, 'Orbital Drift', 'Grand Palais Cinema', now()->subDays(3));

    $this->actingAs($alex);

    $page = visitLocal('/showings')
        ->assertSee('End of list');

    $page->click('Stubs');

    $page->assertSee('1 Films')
        ->assertSee((string) now()->subDays(3)->year)
        ->assertSee('Orbital Drift')
        ->assertDontSee('End of list')
        ->assertNoJavaScriptErrors();
});

it('loads the browse page', function () {
    [$alex] = BrowserFixtures::users();
    BrowserFixtures::showing($alex, 'Orbital Drift', 'Grand Palais Cinema', now()->subDays(3));

    $this->actingAs($alex);

    visitLocal('/browse')
        ->assertSee('The Catalog')
        ->assertSee('Science Fiction')
        ->assertSee('Jane Lead')
        ->assertNoJavaScriptErrors();
});

it('loads the wrapped page for a year', function () {
    [$alex] = BrowserFixtures::users();
    $year = 2025;
    BrowserFixtures::showing($alex, 'Orbital Drift', 'Grand Palais Cinema', now()->setDate($year, 6, 14)->setTime(19, 30));

    $this->actingAs($alex);

    visitLocal("/wrapped/{$year}")
        ->assertSee("Wrapped {$year}")
        ->assertSee('Your Cinematic Journey')
        ->assertDontSee("No movies recorded in {$year}.")
        ->assertNoJavaScriptErrors();
});

it('toggles a bingo goal', function () {
    [$alex] = BrowserFixtures::users();
    $year = now()->year;

    foreach (range(1, 25) as $position) {
        BingoGoal::create([
            'year' => $year,
            'position' => $position,
            'type' => $position === 13 ? 'free_square' : 'genre',
            'target_value' => $position === 13 ? null : "Genre {$position}",
            'title' => $position === 13 ? 'Free Square' : "Goal {$position}",
            'is_completed' => $position === 13,
        ]);
    }

    $this->actingAs($alex);

    $page = visitLocal('/bingo')
        ->assertSee('The Gauntlet Progress')
        ->assertSee('1 / 25 (4%)');

    $page->click('Goal 1');

    $page->assertSee('2 / 25 (8%)')
        ->assertNoJavaScriptErrors();

    $this->assertDatabaseHas('bingo_goals', ['year' => $year, 'position' => 1, 'is_completed' => true]);
});

it('loads the watchlist page', function () {
    [$alex] = BrowserFixtures::users();

    $this->actingAs($alex);

    visitLocal('/watchlist')
        ->assertSee('Anticipated Watchlist')
        ->assertNoJavaScriptErrors();
});
