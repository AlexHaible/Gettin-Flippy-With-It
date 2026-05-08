<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService
{
    protected string $baseUrl = 'https://api.themoviedb.org/3';

    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
    }

    public function searchMovie(string $title): ?array
    {
        if (! $this->apiKey) {
            return null;
        }

        $response = Http::get("{$this->baseUrl}/search/movie", [
            'api_key' => $this->apiKey,
            'query' => $title,
        ]);

        if ($response->successful()) {
            return $response->json('results.0');
        }

        return null;
    }

    public function getMovieDetails(int $tmdbId): ?array
    {
        if (! $this->apiKey) {
            return null;
        }

        $response = Http::get("{$this->baseUrl}/movie/{$tmdbId}", [
            'api_key' => $this->apiKey,
            'append_to_response' => 'credits',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
