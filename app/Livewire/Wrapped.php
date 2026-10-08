<?php

namespace App\Livewire;

use App\Services\Stats\WrappedStats;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Wrapped extends Component
{
    #[Url]
    public int $year;

    public function mount(?int $year = null): void
    {
        $this->year = $year ?? now()->year;
    }

    public function render(WrappedStats $stats): View
    {
        return view('livewire.wrapped', $stats->forYear($this->year));
    }
}
