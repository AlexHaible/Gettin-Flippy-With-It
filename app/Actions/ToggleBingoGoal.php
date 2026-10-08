<?php

namespace App\Actions;

use App\Models\BingoGoal;

class ToggleBingoGoal
{
    /**
     * Flip a goal's completed state. The free square always stays as it is.
     */
    public function __invoke(BingoGoal $goal): void
    {
        if ($goal->type === 'free_square') {
            return;
        }

        $goal->update(['is_completed' => ! $goal->is_completed]);
    }
}
