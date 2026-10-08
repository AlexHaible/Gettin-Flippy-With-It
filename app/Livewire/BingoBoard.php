<?php

namespace App\Livewire;

use App\Actions\ToggleBingoGoal;
use App\Models\BingoGoal;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BingoBoard extends Component
{
    public int $year;

    public function mount(): void
    {
        $this->year = now()->year;
    }

    public function toggle(int $goalId, ToggleBingoGoal $toggleBingoGoal): void
    {
        $toggleBingoGoal(BingoGoal::findOrFail($goalId));
    }

    public function render(): View
    {
        $goals = BingoGoal::where('year', $this->year)
            ->orderBy('position')
            ->with('showing.movie')
            ->get();

        $completedCount = $goals->where('is_completed', true)->count();
        $progressPct = round(($completedCount / 25) * 100);

        return view('livewire.bingo-board', [
            'goals' => $goals,
            'completedCount' => $completedCount,
            'progressPct' => $progressPct,
        ]);
    }
}
