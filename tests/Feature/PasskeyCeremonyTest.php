<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\LaravelPasskeys\Models\Passkey;
use Tests\Support\FakeAuthenticator;
use Tests\TestCase;

/**
 * Drives the real WebAuthn ceremony (spatie/laravel-passkeys + web-auth/webauthn-lib, no mocks)
 * with a software authenticator that produces genuine ES256 signatures.
 */
class PasskeyCeremonyTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_ceremony_stores_a_passkey_for_the_new_user(): void
    {
        $start = $this->postJson('/auth/start', ['username' => 'alex'])
            ->assertOk()
            ->assertJsonPath('flow', 'register');

        $options = $start->json('options');
        $this->assertSame($this->relyingPartyId(), $options['rp']['id']);

        // Browsers refuse to create a credential unless at least one public-key algorithm is offered.
        $algorithms = array_column($options['pubKeyCredParams'], 'alg');
        $this->assertContains(-7, $algorithms, 'ES256 must be offered');
        $this->assertContains(-257, $algorithms, 'RS256 must be offered');
        $this->assertSame(['public-key'], array_unique(array_column($options['pubKeyCredParams'], 'type')));

        $authenticator = new FakeAuthenticator;

        $this->assertCeremonySucceeded($this->finish($authenticator->register($options)));

        $user = User::where('username', 'alex')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        // Regression guard: the passkey must be reachable through User::passkeys().
        $this->assertSame(1, $user->passkeys()->count());
        $passkey = $user->passkeys()->firstOrFail();
        $this->assertSame($user->id, (int) $passkey->authenticatable_id);
        $this->assertSame($authenticator->credentialId, $passkey->data->publicKeyCredentialId);
        $this->assertSame(1, Passkey::count());
    }

    public function test_login_ceremony_authenticates_with_a_registered_passkey(): void
    {
        [$user, $authenticator] = $this->registerAndLogOut('alex');

        $options = $this->startLogin('alex');

        $this->assertCeremonySucceeded($this->finish($authenticator->login($options)));

        $this->assertAuthenticatedAs($user);

        $passkey = $user->passkeys()->firstOrFail();
        $this->assertSame(1, $passkey->data->counter);
        $this->assertNotNull($passkey->last_used_at);
    }

    public function test_login_fails_when_the_assertion_is_signed_with_a_different_key(): void
    {
        [, $authenticator] = $this->registerAndLogOut('alex');

        $options = $this->startLogin('alex');

        $this->finish($authenticator->withDifferentKeyPair()->login($options))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Authentication failed. Passkey could not be verified.');

        $this->assertGuest();
    }

    public function test_login_fails_when_the_client_data_challenge_does_not_match(): void
    {
        [, $authenticator] = $this->registerAndLogOut('alex');

        $options = $this->startLogin('alex');
        $options['challenge'] = FakeAuthenticator::base64Url(random_bytes(16));

        $this->finish($authenticator->login($options))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Authentication failed. Passkey could not be verified.');

        $this->assertGuest();
    }

    public function test_registration_fails_when_the_client_data_challenge_does_not_match(): void
    {
        $options = $this->postJson('/auth/start', ['username' => 'alex'])
            ->assertJsonPath('flow', 'register')
            ->json('options');
        $options['challenge'] = FakeAuthenticator::base64Url(random_bytes(16));

        $this->finish((new FakeAuthenticator)->register($options))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Authentication failed. The given passkey could not be validated. Please check the format and try again.');

        $this->assertGuest();
        $this->assertSame(0, Passkey::count());
    }

    public function test_login_fails_when_the_passkey_belongs_to_another_user(): void
    {
        [$alex] = $this->registerAndLogOut('alex');
        [$casper, $caspersAuthenticator] = $this->registerAndLogOut('casper');
        $this->assertNotSame($alex->id, $casper->id);

        // Start logging in as alex, but answer with casper's (valid) passkey.
        $options = $this->startLogin('alex');

        $this->finish($caspersAuthenticator->login($options))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Authentication failed. Passkey does not belong to the provided username.');

        $this->assertGuest();
    }

    /**
     * Run the full registration ceremony for a new user, then log out again.
     *
     * @return array{User, FakeAuthenticator}
     */
    private function registerAndLogOut(string $username): array
    {
        $options = $this->postJson('/auth/start', ['username' => $username])
            ->assertJsonPath('flow', 'register')
            ->json('options');

        $authenticator = new FakeAuthenticator;

        $this->assertCeremonySucceeded($this->finish($authenticator->register($options)));

        $user = User::where('username', $username)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, $user->passkeys()->count());

        $this->get('/logout')->assertRedirect('/');
        $this->assertGuest();

        return [$user, $authenticator];
    }

    /**
     * @return array<string, mixed>
     */
    private function startLogin(string $username): array
    {
        $options = $this->postJson('/auth/start', ['username' => $username])
            ->assertOk()
            ->assertJsonPath('flow', 'login')
            ->json('options');

        $this->assertSame($this->relyingPartyId(), $options['rpId']);

        return $options;
    }

    private function relyingPartyId(): string
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }

    /**
     * @param  array<string, mixed>  $credential
     */
    private function finish(array $credential): TestResponse
    {
        return $this->postJson('/auth/finish', ['data' => $credential]);
    }

    private function assertCeremonySucceeded(TestResponse $response): void
    {
        // On failure, surface the library's reason from the 422 body.
        $this->assertSame(200, $response->status(), 'Ceremony failed: '.$response->json('message'));
        $response->assertExactJson(['redirect' => '/']);
    }
}
