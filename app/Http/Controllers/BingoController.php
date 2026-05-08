<?php

namespace App\Http\Controllers;

use App\Models\BingoGoal;

class BingoController extends Controller
{
    public function index()
    {
        return view('bingo');
    }
}
