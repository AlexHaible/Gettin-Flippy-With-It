<?php

namespace App\Livewire;

use App\Models\Showing;
use Livewire\Component;

class ShowingsList extends Component
{
    public int $perPage = 15;

    public function loadMore()
    {
        $this->perPage += 15;
    }

    public function render()
    {
        return view('livewire.showings-list', [
            'showings' => Showing::with(['movie', 'cinema'])
                ->orderBy('start_time', 'desc')
                ->paginate($this->perPage)
        ])->layout('components.layouts.app');
    }
}
