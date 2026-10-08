<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;

class FinishPasskeyRegistration
{
    public function __construct(private StorePasskeyAction $storePasskey) {}

    /**
     * Verify the browser's registration response and store the passkey.
     */
    public function __invoke(string $responseJson, string $host): User
    {
        $user = User::findOrFail(session('auth_user_id'));

        $this->storePasskey->execute(
            $user,
            $responseJson,
            session('passkey-registration-options'),
            $host,
            ['name' => $user->username.'-'.Str::uuid()],
        );

        return $user;
    }
}
