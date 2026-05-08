<?php

namespace App\Http\Controllers;

use App\Models\Showing;

class ArchiveController extends Controller
{
    public function index()
    {
        $showings = Showing::with(['movie', 'cinema', 'ratings'])
            ->orderByDesc('start_time')
            ->get();

        return view('archive', compact('showings'));
    }
}
