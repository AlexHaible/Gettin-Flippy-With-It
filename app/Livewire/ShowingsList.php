<?php

namespace App\Livewire;

use App\Models\Rating;
use App\Models\Showing;
use Livewire\Component;

class ShowingsList extends Component
{
    public int $perPage = 15;

    public ?Showing $selectedShowing = null;

    public bool $showModal = false;

    public function loadMore()
    {
        $this->perPage += 15;
    }

    public function openModal(int $showingId)
    {
        $this->selectedShowing = Showing::with(['movie', 'cinema', 'ratings.user'])->find($showingId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedShowing = null;
    }

    public function rateShowing(string $score)
    {
        if (! $this->selectedShowing) {
            return;
        }

        Rating::updateOrCreate(
            ['showing_id' => $this->selectedShowing->id, 'user_id' => auth()->id()],
            ['score' => $score]
        );

        $this->selectedShowing->refresh();
        $this->selectedShowing->load(['movie', 'cinema', 'ratings.user']);
    }

    public function render()
    {
        return view('livewire.showings-list', [
            'showings' => Showing::with(['movie', 'cinema', 'ratings.user'])
                ->orderBy('start_time', 'desc')
                ->paginate($this->perPage),
        ])->layout('components.layouts.app');
    }
}
