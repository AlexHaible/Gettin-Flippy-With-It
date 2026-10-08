<?php

use Tests\Support\BrowserFixtures;

it('renders the dashboard stats with javascript running', function () {
    [$alex] = BrowserFixtures::users();
    BrowserFixtures::showing($alex, 'Orbital Drift', 'Grand Palais Cinema', now()->subDays(3));

    $this->actingAs($alex);

    visitLocal('/dashboard')
        ->assertSee('Total Movies')
        ->assertSee('Orbital Drift')
        ->assertSee('Grand Palais Cinema')
        ->assertScript('typeof window.Livewire', 'object')
        ->assertNoJavaScriptErrors();
});
