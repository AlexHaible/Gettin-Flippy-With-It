<?php

namespace Tests\Support;

use App\Services\TmdbService;

/**
 * In-process stand-in for the TMDB API, bound into the container by browser tests.
 */
class FakeTmdbService extends TmdbService
{
    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    public function __construct(private array $results = [])
    {
        //
    }

    public function searchMovies(string $title): array
    {
        return $this->results;
    }

    public function getMovieDetails(int $tmdbId): ?array
    {
        foreach ($this->results as $result) {
            if ($result['id'] === $tmdbId) {
                return [...$result, 'belongs_to_collection' => null];
            }
        }

        return null;
    }

    public function getNowPlaying(): array
    {
        return [];
    }

    public function getUpcoming(): array
    {
        return [];
    }

    public function getCollection(int $collectionId): ?array
    {
        return null;
    }
}
