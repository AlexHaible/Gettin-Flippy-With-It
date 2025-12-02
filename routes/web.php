<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;

Route::get('/', function () {
    return view('index');
});

WebAuthnRoutes::register(
    attest: 'auth/register',
    assert: 'auth/login'
)->withoutMiddleware(VerifyCsrfToken::class);
