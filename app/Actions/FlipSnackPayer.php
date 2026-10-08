<?php

namespace App\Actions;

use App\Models\Showing;
use App\Models\User;

class FlipSnackPayer
{
    /**
     * Make $user the current snack payer.
     *
     * Flipping confirms that the previous payer paid for the current event,
     * so the showing closest to now is attributed to them first.
     *
     * Returns the new payer, or null when $user already is the payer.
     */
    public function __invoke(User $user): ?User
    {
        if ($user->is_current_payer) {
            return null;
        }

        $previousPayer = User::where('is_current_payer', true)->first();

        if ($previousPayer) {
            Showing::closestTo(now())?->update([
                'popcorn_payer_id' => $previousPayer->id,
                'soda_payer_id' => $previousPayer->id,
            ]);

            $previousPayer->update(['is_current_payer' => false]);
        }

        $user->update(['is_current_payer' => true]);

        return $user;
    }
}
