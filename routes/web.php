<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

    // API Endpoints
    Route::post('/auth/start', [AuthController::class, 'start'])->name('auth.start');
    Route::post('/auth/finish', [AuthController::class, 'finish'])->name('auth.finish');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
