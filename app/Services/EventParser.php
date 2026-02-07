<?php

namespace App\Services;

use Gemini\Contracts\ClientContract;

class EventParser
{
    public function __construct(protected ?ClientContract $client = null)
    {
    }

    public function parse(string $title, string $location, string $description): array
    {
        // 1. Construct the prompt
        $prompt = "You are a helpful assistant that parses Google Calendar event descriptions for a movie night.\n" .
            "The content may be in English or Danish. Please extract the following information:\n" .
            "- Movie Title (Use the event title '$title' as a strong hint, but check description for full name)\n" .
            "- Cinema Name (Check location '$location' and description)\n" .
            "- Hall/Screen Name (Check title '$title' for 'Sal' or 'Screen', and description)\n" .
            "- Total Price (as integer)\n" .
            "- Who paid for the tickets (Name)\n" .
            "- Who paid for snacks (Popcorn & Soda are always bundled)\n" .
            "- Booking Reference\n" .
            "- Seat Numbers (comma separated)\n\n" .
            "Special Rules:\n" .
            "- If the title contains 'IMAX', set Cinema to 'Vue Fisketorvet' and Hall to 'IMAX'.\n" .
            "- If the location or description mentions 'CinemaxX', set Cinema to 'Vue Fisketorvet' (because it rebranded).\n" .
            "- Extract 'Sal' number from title (e.g. 'Toy Story 4 - Sal 12' -> Hall: 'Sal 12').\n\n" .
            "If a payer is not explicitly mentioned:\n" .
            "- Return null for the payer fields.\n" .
            "The user \"Alex\" corresponds to ID 1. The friend \"Casper\" (or any other name) corresponds to ID 2.\n\n" .
            "Output JSON only.\n" .
            "Structure:\n" .
            "{\n" .
            "    \"movie\": \"Title\",\n" .
            "    \"cinema\": \"Cinema\",\n" .
            "    \"hall\": \"Hall\",\n" .
            "    \"price\": 300,\n" .
            "    \"ticket_payer\": \"Name\",\n" .
            "    \"snack_payer\": \"Name\",\n" .
            "    \"booking_reference\": \"Ref\",\n" .
            "    \"seats\": \"C1, C2\"\n" .
            "}\n\n" .
            "Event Details:\n" .
            "Title: $title\n" .
            "Location: $location\n" .
            "Description:\n" .
            $description;

        // 2. Call Gemini API
        if (!$this->client) {
            $apiKey = config('llm.api_key') ?? env('GEMINI_API_KEY');
            if (!$apiKey) {
                throw new \Exception('Gemini API Key not specified in config or .env');
            }
            $this->client = \Gemini::client($apiKey);
        }

        $response = retry(3, function () use ($prompt) {
            return $this->client->generativeModel('models/gemini-3-flash-preview')->generateContent($prompt);
        }, 1000);

        // 3. Parse JSON from response
        return $this->extractJson($response->text());
    }

    private function extractJson(string $text): array
    {
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            return json_decode($matches[1], true) ?? [];
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start !== false && $end !== false) {
            $jsonStr = substr($text, $start, $end - $start + 1);
            return json_decode($jsonStr, true) ?? [];
        }

        return [];
    }
}