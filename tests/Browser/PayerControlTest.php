<?php

use App\Models\User;
use Tests\Support\BrowserFixtures;

it('flips the snack payer through a livewire round trip', function () {
    [$alex, $casper] = BrowserFixtures::users(alexIsPayer: true);
    $showing = BrowserFixtures::showing($alex, 'Orbital Drift', 'Grand Palais Cinema', now()->subHour());

    $this->actingAs($casper);

    $page = visitLocal('/')
        ->assertSee('Current Patron')
        ->assertSee('Alex')
        ->assertDontSee('The Honor is Yours');

    $page->click('Alex');

    $page->assertSee('The Honor is Yours')
        ->assertSee('Casper')
        ->assertDontSee('Alex')
        ->assertNoJavaScriptErrors();

    // User has no boolean cast for is_current_payer, so compare at the database level.
    $this->assertDatabaseHas('users', ['id' => User::CASPER_ID, 'is_current_payer' => true]);
    $this->assertDatabaseHas('users', ['id' => User::ALEX_ID, 'is_current_payer' => false]);

    expect($showing->fresh()->popcorn_payer_id)->toBe(User::ALEX_ID)
        ->and($showing->fresh()->soda_payer_id)->toBe(User::ALEX_ID);
});
