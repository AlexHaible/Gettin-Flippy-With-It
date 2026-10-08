<?php

namespace App\Actions\Auth;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;

class FinishPasskeyLogin
{
    public function __construct(private FindPasskeyToAuthenticateAction $findPasskey) {}

    /**
     * Verify the browser's assertion and return the user it belongs to.
     *
     * @throws Exception when verification fails or the passkey belongs to another user
     */
    public function __invoke(string $responseJson): Authenticatable
    {
        $optionsJson = session('passkey-authentication-options');

        if (! $optionsJson) {
            throw new RuntimeException('Missing authentication options in session.');
        }

        $passkey = $this->findPasskey->execute($responseJson, $optionsJson);

        if (! $passkey) {
            throw new Exception('Passkey could not be verified.');
        }

        Log::debug('Passkey resolved by FindPasskeyToAuthenticateAction', [
            'passkey_id' => $passkey->id,
            'user_id' => $passkey->user?->id,
        ]);

        $authenticatableModel = config('passkeys.models.authenticatable', User::class);

        $user = $passkey->user
            ?? ($authenticatableModel ? $authenticatableModel::find($passkey->authenticatable_id) : null);

        $expectedUserId = session('auth_user_id');

        Log::debug('Resolved user from passkey', [
            'user_id' => $user?->id,
            'username' => $user?->username,
            'expected_user_id' => $expectedUserId,
        ]);

        if (! $user) {
            throw new Exception('No user attached to this passkey.');
        }

        if ($expectedUserId && $user->id !== $expectedUserId) {
            throw new Exception('Passkey does not belong to the provided username.');
        }

        return $user;
    }
}
