<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Tests\TestCase;

class PasskeyAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_requires_a_username(): void
    {
        $this->postJson('/auth/start', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('username');

        $this->postJson('/auth/start', ['username' => str_repeat('a', 256)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('username');
    }

    public function test_finish_requires_data(): void
    {
        $this->postJson('/auth/finish', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('data');
    }

    public function test_start_creates_an_unknown_user_and_returns_registration_options(): void
    {
        $this->mock(GeneratePasskeyRegisterOptionsAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->andReturn('{"challenge":"abc"}');
        });

        $this->postJson('/auth/start', ['username' => 'newbie'])
            ->assertOk()
            ->assertExactJson(['flow' => 'register', 'options' => ['challenge' => 'abc']])
            ->assertSessionHas('auth_action', 'register')
            ->assertSessionHas('passkey-registration-options', '{"challenge":"abc"}');

        $user = User::where('username', 'newbie')->firstOrFail();
        $this->assertSame($user->id, session('auth_user_id'));
    }

    public function test_start_returns_login_options_for_a_known_user(): void
    {
        $user = User::factory()->create(['username' => 'alex']);

        $this->mock(GeneratePasskeyAuthenticationOptionsAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->andReturn('{"challenge":"xyz"}');
        });

        $this->postJson('/auth/start', ['username' => 'alex'])
            ->assertOk()
            ->assertExactJson(['flow' => 'login', 'options' => ['challenge' => 'xyz']])
            ->assertSessionHas('auth_action', 'login')
            ->assertSessionHas('auth_user_id', $user->id)
            ->assertSessionHas('passkey-authentication-options', '{"challenge":"xyz"}');

        $this->assertSame(1, User::count());
    }

    public function test_finish_login_without_options_in_session_fails_with_422(): void
    {
        $this->withSession(['auth_action' => 'login'])
            ->postJson('/auth/finish', ['data' => ['id' => 'abc']])
            ->assertStatus(422)
            ->assertExactJson(['message' => 'Authentication failed. Missing authentication options in session.']);

        $this->assertGuest();
    }
}
