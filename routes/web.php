<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

    // API Endpoints
    Route::post('/auth/start', [AuthController::class, 'start'])->name('auth.start');
    Route::post('/auth/finish', [AuthController::class, 'finish'])->name('auth.finish');
});

use App\Livewire\ShowingsList;
use App\Http\Controllers\WrappedController;

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/showings', ShowingsList::class)->name('showings.index');
    Route::get('/wrapped/{year?}', [WrappedController::class, 'show'])->name('wrapped');
});
