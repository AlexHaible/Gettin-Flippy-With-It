<?php

namespace App\Http\Controllers;

use App\Models\Showing;

class EntityController extends Controller
{
    public function actor(string $name)
    {
        $showings = Showing::with(['movie', 'cinema'])
            ->whereHas('movie', function ($query) use ($name) {
                // Since cast is a JSON string of an array of strings, we can use LIKE
                // Or SQLite JSON extraction if needed. LIKE is safe enough for exact name matches.
                $query->where('cast', 'LIKE', '%"'.$name.'"%');
            })
            ->orderByDesc('start_time')
            ->get();

        return view('entity', [
            'entityType' => 'Actor',
            'entityName' => $name,
            'showings' => $showings,
        ]);
    }

    public function genre(string $name)
    {
        $showings = Showing::with(['movie', 'cinema'])
            ->whereHas('movie', function ($query) use ($name) {
                $query->where('genres', 'LIKE', '%"'.$name.'"%');
            })
            ->orderByDesc('start_time')
            ->get();

        return view('entity', [
            'entityType' => 'Genre',
            'entityName' => $name,
            'showings' => $showings,
        ]);
    }
}
