<?php

namespace App\Services;

use Gemini\Contracts\ClientContract;
use Gemini\Data\FunctionDeclaration;
use Gemini\Data\Schema;
use Gemini\Data\Tool;
use Gemini\Enums\DataType;

class EventParser
{
    public function __construct(protected ?ClientContract $client = null) {}

    public function parse(string $title, string $location, string $description): array
    {
        // 1. Initialize Client
        if (! $this->client) {
            $apiKey = config('services.gemini.api_key');
            if (! $apiKey) {
                throw new \Exception('Gemini API Key not specified in config/services.php (.env GEMINI_API_KEY)');
            }
            $this->client = \Gemini::client($apiKey);
        }

        // 2. Define the Function Tool
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'movie' => new Schema(
                    type: DataType::STRING,
                    description: 'The full title of the movie.'
                ),
                'cinema' => new Schema(
                    type: DataType::STRING,
                    description: 'The name of the cinema (e.g. Vue Fisketorvet).'
                ),
                'hall' => new Schema(
                    type: DataType::STRING,
                    description: 'The hall or screen name (e.g. Sal 1, IMAX).'
                ),
                'price' => new Schema(
                    type: DataType::INTEGER,
                    description: 'The total price in DKK.'
                ),
                'ticket_payer' => new Schema(
                    type: DataType::STRING,
                    description: 'Name of the person who paid for tickets (Alex or Casper). Return null if unknown.',
                    nullable: true
                ),
                'snack_payer' => new Schema(
                    type: DataType::STRING,
                    description: 'Name of the person who paid for snacks. Return null if unknown.',
                    nullable: true
                ),
                'booking_reference' => new Schema(
                    type: DataType::STRING,
                    description: 'The booking reference number.',
                    nullable: true
                ),
                'seats' => new Schema(
                    type: DataType::STRING,
                    description: 'Comma separated seat numbers (e.g. C1, C2).',
                    nullable: true
                ),
            ],
            required: ['movie', 'cinema', 'hall', 'price']
        );

        $functionDeclaration = new FunctionDeclaration(
            name: 'extract_showing_data',
            description: 'Extracts movie showing details from a calendar event.',
            parameters: $schema
        );

        $tool = new Tool(
            functionDeclarations: [$functionDeclaration]
        );

        // 3. Construct the Prompt
        $prompt = "Analyze this calendar event and extract the movie showing details.\n".
            "Event Title: $title\n".
            "Location: $location\n".
            "Description: $description\n\n".
            "Special Rules:\n".
            "- If 'IMAX' is in the title, Cinema is 'Vue Fisketorvet' and Hall is 'IMAX'.\n".
            "- 'CinemaxX' is now 'Vue Fisketorvet'.\n".
            "- Extract 'Sal' number from title if present.\n".
            "- Alex = ID 1, Casper = ID 2 (map names in output if possible, otherwise just names).\n".
            'Use the extract_showing_data function.';

        // 4. Call Gemini API
        $response = retry(3, function () use ($prompt, $tool) {
            return $this->client
                ->generativeModel('models/gemini-3-flash-preview')
                ->withTool($tool)
                ->generateContent($prompt);
        }, 1000);

        // 5. Extract Function Call Arguments
        $parts = $response->parts();
        $part = $parts[0] ?? null;

        if ($part && $part->functionCall && $part->functionCall->name === 'extract_showing_data') {
            return $part->functionCall->args;
        }

        return [];
    }
}
