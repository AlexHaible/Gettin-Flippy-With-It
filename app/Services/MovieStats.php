<?php

namespace App\Services;

use App\Models\Movie;
use Illuminate\Support\Collection;

class MovieStats
{
    /** @var Collection<int, Movie>|null */
    private ?Collection $movies = null;

    /**
     * Number of showings per genre, sorted from most to least watched.
     *
     * When $includeUnwatched is true, genres that only appear on movies
     * without showings are listed with a count of 0.
     *
     * @return array<string, int>
     */
    public function genreCounts(bool $includeUnwatched = false): array
    {
        return $this->countBy('genres', $includeUnwatched);
    }

    /**
     * Number of showings per actor, sorted from most to least watched.
     *
     * When $includeUnwatched is true, actors that only appear in movies
     * without showings are listed with a count of 0.
     *
     * @return array<string, int>
     */
    public function actorCounts(bool $includeUnwatched = false): array
    {
        return $this->countBy('cast', $includeUnwatched);
    }

    /**
     * @return array<string, int>
     */
    private function countBy(string $attribute, bool $includeUnwatched): array
    {
        $counts = [];

        foreach ($this->movies() as $movie) {
            if (! $includeUnwatched && $movie->showings_count == 0) {
                continue;
            }

            foreach ($movie->{$attribute} ?? [] as $name) {
                $counts[$name] = ($counts[$name] ?? 0) + $movie->showings_count;
            }
        }

        arsort($counts);

        return $counts;
    }

    /**
     * @return Collection<int, Movie>
     */
    private function movies(): Collection
    {
        return $this->movies ??= Movie::withCount('showings')->get();
    }
}
