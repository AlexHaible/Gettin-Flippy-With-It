<?php

namespace App\Services;

use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\RateLimitException;
use Anthropic\ServiceContracts\MessagesContract;
use App\Services\EventParser\ShowingDetails;
use Illuminate\Support\Facades\Log;
use Throwable;

class EventParser
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
You analyze calendar events and extract movie showing details.

Special Rules:
- If 'IMAX' is in the title, Cinema is 'Vue Fisketorvet' and Hall is 'IMAX'.
- 'CinemaxX' is now 'Vue Fisketorvet'.
- Extract 'Sal' number from title if present.
- The only two people are Alex and Casper; return payer fields as those names, or null when the event does not say.
PROMPT;

    public function __construct(protected MessagesContract $messages) {}

    /**
     * @return array{movie?: string, cinema?: string, hall?: string, price?: int, ticket_payer?: ?string, snack_payer?: ?string, booking_reference?: ?string, seats?: ?string}
     */
    public function parse(string $title, string $location, string $description): array
    {
        $prompt = "Event Title: $title\nLocation: $location\nDescription: $description";

        $message = retry(
            3,
            fn () => $this->messages->create(
                maxTokens: 2048,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: (string) config('services.anthropic.model'),
                outputConfig: ['effort' => 'low', 'format' => ShowingDetails::class],
                system: self::SYSTEM_PROMPT,
            ),
            1000,
            $this->isRetryable(...),
        );

        if ($message->stopReason === 'refusal') {
            Log::warning('Claude refused to parse calendar event.', ['title' => $title]);

            return [];
        }

        $parsed = $message->parsedOutput();

        if (! $parsed instanceof ShowingDetails) {
            return [];
        }

        return $parsed->toArray();
    }

    private function isRetryable(Throwable $e): bool
    {
        return $e instanceof RateLimitException
            || $e instanceof APIConnectionException
            || ($e instanceof APIStatusException && ($e->status ?? 0) >= 500);
    }
}
