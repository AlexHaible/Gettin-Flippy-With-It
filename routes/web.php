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

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BingoController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\WrappedController;
use App\Livewire\ShowingsList;
use App\Livewire\Watchlist;

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/showings', ShowingsList::class)->name('showings');
    Route::get('/watchlist', Watchlist::class)->name('watchlist');
    Route::get('/wrapped/{year?}', [WrappedController::class, 'show'])->name('wrapped');

    Route::get('/actor/{name}', [EntityController::class, 'actor'])->name('actor');
    Route::get('/genre/{name}', [EntityController::class, 'genre'])->name('genre');
    Route::get('/bingo', [\App\Http\Controllers\BingoController::class, 'index'])->name('bingo');
    Route::post('/bingo/{goal}/toggle', [\App\Http\Controllers\BingoController::class, 'toggle'])->name('bingo.toggle');
    Route::get('/archive', fn() => redirect()->route('showings'))->name('archive');
});
