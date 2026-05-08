<?php

namespace App\Services;

use App\Models\BingoGoal;
use App\Models\Showing;

class BingoService
{
    /**
     * Evaluate a newly saved Showing against all uncompleted bingo goals for the current year.
     */
    public function evaluate(Showing $showing): void
    {
        $year = $showing->start_time->year;
        $goals = BingoGoal::where('year', $year)->where('is_completed', false)->get();

        $movie   = $showing->movie;
        $ratings = $showing->ratings;
        $genres  = json_decode($movie->genres ?? '[]', true) ?? [];
        $cast    = json_decode($movie->cast ?? '[]', true) ?? [];

        foreach ($goals as $goal) {
            $completed = match ($goal->type) {
                'genre'          => in_array($goal->target_value, $genres),
                'actor'          => in_array($goal->target_value, $cast),
                'cinema'         => $showing->cinema?->name === $goal->target_value,
                'runtime'        => ($movie->runtime ?? 0) >= (int) $goal->target_value,
                'mutual_liked'   => $ratings->where('score', 'liked')->count() >= 2,
                'mutual_disliked'=> $ratings->where('score', 'disliked')->count() >= 2,
                'free_square'    => true,
                default          => false,
            };

            if ($completed) {
                $goal->update(['is_completed' => true, 'showing_id' => $showing->id]);
            }
        }
    }
}
