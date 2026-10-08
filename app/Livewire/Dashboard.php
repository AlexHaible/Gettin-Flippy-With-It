<?php

namespace App\Livewire;

use App\Services\Stats\DashboardStats;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(DashboardStats $stats): View
    {
        return view('livewire.dashboard', $stats->toArray());
    }
}
