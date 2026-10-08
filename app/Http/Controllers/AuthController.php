<?php

namespace App\Http\Controllers;

use App\Actions\Auth\FinishPasskeyLogin;
use App\Actions\Auth\FinishPasskeyRegistration;
use App\Actions\Auth\StartPasskeyLogin;
use App\Actions\Auth\StartPasskeyRegistration;
use App\Http\Requests\Auth\FinishPasskeyRequest;
use App\Http\Requests\Auth\StartPasskeyRequest;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Step 1: return WebAuthn options for registration (unknown username) or login (known username).
     */
    public function start(StartPasskeyRequest $request, StartPasskeyRegistration $register, StartPasskeyLogin $login): JsonResponse
    {
        $username = $request->validated('username');
        $user = User::where('username', $username)->first();

        return response()->json($user ? $login($user) : $register($username));
    }

    /**
     * Step 2: verify the browser's WebAuthn response and log the user in.
     */
    public function finish(FinishPasskeyRequest $request, FinishPasskeyRegistration $register, FinishPasskeyLogin $login): JsonResponse
    {
        $responseJson = json_encode($request->validated('data'));

        try {
            $user = session('auth_action') === 'register'
                ? $register($responseJson, $request->getHost())
                : $login($responseJson);

            Auth::login($user, true);

            session()->forget([
                'auth_action',
                'auth_user_id',
                'passkey-registration-options',
                'passkey-authentication-options',
            ]);

            return response()->json(['redirect' => '/']);
        } catch (Exception $e) {
            Log::error('Passkey Error: '.$e->getMessage());

            return response()->json(['message' => 'Authentication failed. '.$e->getMessage()], 422);
        }
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect('/');
    }
}
