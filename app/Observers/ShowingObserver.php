<?php

namespace App\Observers;

use App\Models\Showing;
use App\Services\BingoService;

class ShowingObserver
{
    public function __construct(protected BingoService $bingoService) {}

    public function saved(Showing $showing): void
    {
        // Reload fresh relations so BingoService always has up-to-date data
        $showing->load(['movie', 'cinema', 'ratings']);

        $this->bingoService->evaluate($showing);
    }
}
