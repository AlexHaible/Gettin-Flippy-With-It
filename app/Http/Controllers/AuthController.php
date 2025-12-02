<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Note: This action automatically flashes 'passkey-authentication-options' to session
        $options = app(GeneratePasskeyAuthenticationOptionsAction::class)->execute();

        session(['auth_action' => 'login']);

        return response()->json([
            'flow' => 'login',
            'options' => json_decode($options)
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

        $action = session('auth_action');
        $responseJson = json_encode($data['data']); // Convert array back to string for the Action

        try {
            if ($action === 'register') {
                $userId = session('auth_user_id');
                $user = User::findOrFail($userId);

                // Retrieve original options from session
                $optionsJson = session('passkey-registration-options');

                // Verify & Store
                app(StorePasskeyAction::class)->execute(
                    authenticatable: $user,
                    passkeyJson: $responseJson,
                    passkeyOptionsJson: $optionsJson,
                    hostName: $request->getHost()
                );

                Auth::login($user, true);

            } else {
                // Retrieve automatically flashed options
                $optionsJson = session('passkey-authentication-options');

                // Verify & Find User
                $passkey = app(FindPasskeyToAuthenticateAction::class)->execute(
                    publicKeyCredentialJson: $responseJson,
                    passkeyOptionsJson: $optionsJson
                );

                if ($passkey && $passkey->user) {
                    Auth::login($passkey->user, true);
                } else {
                    throw new \Exception("Passkey not found or invalid.");
                }
            }

            session()->forget(['auth_action', 'auth_user_id', 'passkey-registration-options']);

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
