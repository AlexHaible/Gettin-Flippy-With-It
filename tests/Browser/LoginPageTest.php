<?php

use App\Models\User;

it('shows the passkey login form to guests', function () {
    visitLocal('/login')
        ->assertSee('Identify Yourself')
        ->assertVisible('#username')
        ->assertSee('Proceed')
        ->assertScript('window.browserSupportsWebAuthn()', true)
        ->assertNoJavaScriptErrors();
});

it('reaches the passkey registration step for a new username', function () {
    $page = visitLocal('/login')
        ->assertSee('Identify Yourself');

    // Record every status message, since the register step is only shown until the
    // browser's WebAuthn call settles. Nothing about the ceremony itself is faked.
    $page->script(<<<'JS'
        () => {
            window.__statusHistory = [];
            const status = document.getElementById('status');
            new MutationObserver(() => window.__statusHistory.push(status.innerText))
                .observe(status, { childList: true, characterData: true, subtree: true });
        }
        JS);

    $page->fill('#username', 'Newcomer');
    $page->click('Proceed');

    // The test server answers on http://127.0.0.1, and WebAuthn refuses IP-address origins
    // ("SecurityError: 127.0.0.1 is an invalid domain"), so the headless ceremony always
    // ends in the page's own failure message.
    $page->assertSee('Authentication failed.')
        ->assertScript('window.__statusHistory.includes("New patron. Registering key...")', true)
        ->assertNoJavaScriptErrors();

    expect(User::where('username', 'Newcomer')->exists())->toBeTrue();
});
