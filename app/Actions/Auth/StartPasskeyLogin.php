<?php

namespace App\Actions\Auth;

use App\Models\User;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;

class StartPasskeyLogin
{
    public function __construct(private GeneratePasskeyAuthenticationOptionsAction $generateOptions) {}

    /**
     * Return the WebAuthn authentication options for an existing user.
     *
     * @return array{flow: string, options: mixed}
     */
    public function __invoke(User $user): array
    {
        $options = $this->generateOptions->execute();

        // Persist the options sent to the browser so the challenge
        // used during verification matches exactly.
        session([
            'auth_action' => 'login',
            'auth_user_id' => $user->id,
            'passkey-authentication-options' => $options,
        ]);

        return [
            'flow' => 'login',
            'options' => json_decode($options),
        ];
    }
}
