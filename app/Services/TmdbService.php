<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
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
        return $this->searchMovies($title)[0] ?? null;
    }

    public function searchMovies(string $title): array
    {
        if (! $this->apiKey) {
            return [];
        }

        $response = $this->request()->get('search/movie', ['query' => $title]);

        return $response->successful() ? $response->json('results', []) : [];
    }

    public function getMovieDetails(int $tmdbId): ?array
    {
        if (! $this->apiKey) {
            return null;
        }

        $response = $this->request()->get("movie/{$tmdbId}", ['append_to_response' => 'credits']);

        return $response->successful() ? $response->json() : null;
    }

    public function getNowPlaying(): array
    {
        if (! $this->apiKey) {
            return [];
        }

        $response = $this->request()->get('movie/now_playing');

        return $response->successful() ? $response->json('results', []) : [];
    }

    public function getUpcoming(): array
    {
        if (! $this->apiKey) {
            return [];
        }

        $response = $this->request()->get('movie/upcoming');

        return $response->successful() ? $response->json('results', []) : [];
    }

    public function getCollection(int $collectionId): ?array
    {
        if (! $this->apiKey) {
            return null;
        }

        $response = $this->request()->get("collection/{$collectionId}");

        return $response->successful() ? $response->json() : null;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)->withQueryParameters(['api_key' => $this->apiKey]);
    }
}
