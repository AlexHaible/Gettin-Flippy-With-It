<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\BingoController;
use App\Livewire\Browse;
use App\Livewire\Dashboard;
use App\Livewire\ShowingsList;
use App\Livewire\Watchlist;
use App\Livewire\Wrapped;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/auth/start', [AuthController::class, 'start'])->name('auth.start');
    Route::post('/auth/finish', [AuthController::class, 'finish'])->name('auth.finish');
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/showings', ShowingsList::class)->name('showings');
    Route::get('/watchlist', Watchlist::class)->name('watchlist');
    Route::get('/wrapped/{year?}', Wrapped::class)->name('wrapped');
    Route::get('/browse', Browse::class)->name('browse');

    Route::get('/actor/{name}', [EntityController::class, 'actor'])->name('actor');
    Route::get('/genre/{name}', [EntityController::class, 'genre'])->name('genre');
    Route::get('/bingo', [BingoController::class, 'index'])->name('bingo');
    Route::get('/archive', fn() => redirect()->route('showings'))->name('archive');
});

