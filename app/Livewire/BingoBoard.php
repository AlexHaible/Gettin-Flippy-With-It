<?php

namespace App\Livewire;

use App\Models\BingoGoal;
use Livewire\Component;

class BingoBoard extends Component
{
    public int $year;

    public function mount()
    {
        $this->year = now()->year;
    }

    public function toggle(int $goalId)
    {
        $goal = BingoGoal::findOrFail($goalId);
        
        if ($goal->type !== 'free_square') {
            $goal->update(['is_completed' => !$goal->is_completed]);
        }
    }

    public function render()
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
