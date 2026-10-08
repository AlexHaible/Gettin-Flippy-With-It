<?php

namespace App\Livewire;

use App\Services\MovieStats;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Browse extends Component
{
    public function render(MovieStats $stats): View
    {
        return view('livewire.browse', [
            'genres' => $stats->genreCounts(includeUnwatched: true),
            'actors' => $stats->actorCounts(includeUnwatched: true),
        ]);
    }
}
