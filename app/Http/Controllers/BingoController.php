<?php

namespace App\Http\Controllers;

use App\Models\BingoGoal;

class BingoController extends Controller
{
    public function index()
    {
        $year = now()->year;
        $goals = BingoGoal::where('year', $year)
            ->orderBy('position')
            ->with('showing.movie')
            ->get();

        $hasBoard = $goals->isNotEmpty();

        return view('bingo', compact('goals', 'year', 'hasBoard'));
    }
}
