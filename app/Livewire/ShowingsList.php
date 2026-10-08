<?php

namespace App\Livewire;

use App\Actions\RateShowing;
use App\Models\Showing;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ShowingsList extends Component
{
    public int $perPage = 15;

    public string $viewMode = 'list'; // 'list' | 'grid'

    public ?Showing $selectedShowing = null;

    public bool $showModal = false;

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function setView(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function openModal(int $showingId): void
    {
        $this->selectedShowing = Showing::with(['movie', 'cinema', 'ratings.user'])->find($showingId);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedShowing = null;
    }

    public function rateShowing(string $score, RateShowing $rateShowing): void
    {
        if (! $this->selectedShowing) {
            return;
        }

        $rateShowing($this->selectedShowing, auth()->user(), $score);

        $this->selectedShowing->refresh();
        $this->selectedShowing->load(['movie', 'cinema', 'ratings.user']);
    }

    public function render(): View
    {
        // Grid mode loads everything at once for grouping by year;
        // list mode uses cursor-based pagination for infinite scroll.
        $showings = $this->viewMode === 'grid'
            ? Showing::with(['movie', 'cinema', 'ratings'])->orderByDesc('start_time')->get()
            : Showing::with(['movie', 'cinema', 'ratings.user'])->orderByDesc('start_time')->paginate($this->perPage);

        return view('livewire.showings-list', compact('showings'));
    }
}
