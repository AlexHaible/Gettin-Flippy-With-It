<?php

namespace App\Actions\Auth;

use App\Models\User;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;

class StartPasskeyRegistration
{
    public function __construct(private GeneratePasskeyRegisterOptionsAction $generateOptions) {}

    /**
     * Create the user and return the WebAuthn registration options for the browser.
     *
     * @return array{flow: string, options: mixed}
     */
    public function __invoke(string $username): array
    {
        $user = User::create([
            'username' => $username,
            'is_current_payer' => false,
        ]);

        $options = $this->generateOptions->execute($user);

        // The finish step needs the exact options that were sent to the browser.
        session([
            'auth_action' => 'register',
            'auth_user_id' => $user->id,
            'passkey-registration-options' => $options,
        ]);

        return [
            'flow' => 'register',
            'options' => json_decode($options),
        ];
    }
}
