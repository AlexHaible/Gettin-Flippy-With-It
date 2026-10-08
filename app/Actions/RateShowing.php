<?php

namespace App\Actions;

use App\Models\Rating;
use App\Models\Showing;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RateShowing
{
    /**
     * Create or update $user's rating of $showing.
     *
     * @throws ValidationException when $score is not one of Rating::SCORES
     */
    public function __invoke(Showing $showing, User $user, string $score): Rating
    {
        Validator::make(
            ['score' => $score],
            ['score' => ['required', Rule::in(Rating::SCORES)]],
        )->validate();

        return Rating::updateOrCreate(
            ['showing_id' => $showing->id, 'user_id' => $user->id],
            ['score' => $score],
        );
    }
}
