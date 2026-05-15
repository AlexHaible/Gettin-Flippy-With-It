<?php

namespace App\Livewire;

use App\Models\Movie;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Browse extends Component
{
    public function render()
    {
        $allMovies = Movie::withCount('showings')->get();
        $genres = [];
        $actors = [];

        foreach ($allMovies as $movie) {
            $movieGenres = json_decode($movie->genres, true) ?? [];
            foreach ($movieGenres as $genre) {
                $genres[$genre] = ($genres[$genre] ?? 0) + $movie->showings_count;
            }

            $movieCast = json_decode($movie->cast, true) ?? [];
            foreach ($movieCast as $actor) {
                $actors[$actor] = ($actors[$actor] ?? 0) + $movie->showings_count;
            }
        }

        arsort($genres);
        arsort($actors);

        return view('livewire.browse', [
            'genres' => $genres,
            'actors' => $actors,
        ]);
    }
}
