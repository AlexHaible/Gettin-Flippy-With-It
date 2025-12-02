<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Step 1: Start the Ceremony.
     * Return Options for either Login or Registration based on Username.
     */
    public function start(Request $request)
    {
        $data = $request->validate(['username' => 'required|string|max:255']);
        $username = $data['username'];

        $user = User::where('username', $username)->first();

        if (!$user) {
            // Create user immediately
            $user = User::create([
                'username' => $username,
                'is_current_payer' => false
            ]);

            // execute() returns the options JSON string
            $options = app(GeneratePasskeyRegisterOptionsAction::class)->execute($user);

            // Store state in session for the finish step
            session([
                'auth_action' => 'register',
                'auth_user_id' => $user->id,
                'passkey-registration-options' => $options // Manual storage required for registration
            ]);

            return response()->json([
                'flow' => 'register',
                'options' => json_decode($options)
            ]);
        }

        // If user exists, start login flow
        $options = app(GeneratePasskeyAuthenticationOptionsAction::class)->execute();

        // Explicitly persist the options we are sending to the browser,
        // so the challenge used during verification matches exactly.
        session([
            'auth_action' => 'login',
            'auth_user_id' => $user->id,
            'passkey-authentication-options' => $options,
        ]);

        return response()->json([
            'flow'    => 'login',
            'options' => json_decode($options),
        ]);
    }

    /**
     * Step 2: Verify the Ceremony.
     */
    public function finish(Request $request)
    {
        $data = $request->validate([
            'data' => 'required', // The WebAuthn JSON response from browser
        ]);

        Log::error('Browser data when authenticating', $data);

        $action = session('auth_action');
        $responseJson = json_encode($data['data']); // Convert array back to string for the Action

        try {
            if ($action === 'register') {
                $userId = session('auth_user_id');
                $user = User::findOrFail($userId);

                // Retrieve original options from session
                $optionsJson = session('passkey-registration-options');

                $passkeyName = $user->username . '-' . Str::uuid();

                app(StorePasskeyAction::class)->execute(
                    $user,
                    $responseJson,
                    $optionsJson,
                    $request->getHost(),
                    ['name' => $passkeyName],
                );

                Auth::login($user, true);

            } else {
                $optionsJson = session('passkey-authentication-options');

                Log::error('Auth options', [
                    'auth_action' => session('auth_action'),
                    'auth_options' => $optionsJson,
                ]);

                if (! $optionsJson) {
                    throw new \RuntimeException('Missing authentication options in session.');
                }

                // Verify & Find User
                $passkey = app(FindPasskeyToAuthenticateAction::class)->execute(
                    $responseJson,
                    $optionsJson,
                );

                Log::error('Passkey resolved by FindPasskeyToAuthenticateAction', [
                    'passkey_id' => $passkey?->id,
                    'user_id' => $passkey?->user?->id,
                ]);

                $authenticatableModel = config('passkeys.models.authenticatable', User::class);

                $user = $passkey->user
                    ?? ($authenticatableModel ? $authenticatableModel::find($passkey->authenticatable_id) : null);

                $expectedUserId = session('auth_user_id');

                Log::error('Resolved user from passkey', [
                    'user_id' => $user?->id,
                    'username' => $user?->username,
                    'expected_user_id' => $expectedUserId,
                ]);

                if (! $user) {
                    throw new \Exception('No user attached to this passkey.');
                }

                if ($expectedUserId && $user->id !== $expectedUserId) {
                    throw new \Exception('Passkey does not belong to the provided username.');
                }

                Auth::login($user, true);
            }

            session()->forget([
                'auth_action',
                'auth_user_id',
                'passkey-registration-options',
                'passkey-authentication-options',
            ]);

            return response()->json(['redirect' => '/']);

        } catch (\Exception $e) {
            logger()->error('Passkey Error: ' . $e->getMessage());
            return response()->json(['message' => 'Authentication failed. ' . $e->getMessage()], 422);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
