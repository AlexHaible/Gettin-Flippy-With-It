<?php

namespace App\Services\EventParser;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Structured output schema for a movie showing extracted from a calendar event.
 */
class ShowingDetails implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'The full title of the movie.')]
    public string $movie;

    #[Constrained(description: 'The name of the cinema (e.g. Vue Fisketorvet).')]
    public string $cinema;

    #[Constrained(description: 'The hall or screen name (e.g. Sal 1, IMAX).')]
    public string $hall;

    #[Constrained(description: 'The total price in DKK.')]
    public int $price;

    #[Constrained(description: 'Name of the person who paid for tickets (Alex or Casper). Null if unknown.')]
    public ?string $ticket_payer = null;

    #[Constrained(description: 'Name of the person who paid for snacks. Null if unknown.')]
    public ?string $snack_payer = null;

    #[Constrained(description: 'The booking reference number.')]
    public ?string $booking_reference = null;

    #[Constrained(description: 'Comma separated seat numbers (e.g. C1, C2).')]
    public ?string $seats = null;

    /**
     * @return array{movie: string, cinema: string, hall: string, price: int, ticket_payer: ?string, snack_payer: ?string, booking_reference: ?string, seats: ?string}
     */
    public function toArray(): array
    {
        return [
            'movie' => $this->movie,
            'cinema' => $this->cinema,
            'hall' => $this->hall,
            'price' => $this->price,
            'ticket_payer' => $this->ticket_payer,
            'snack_payer' => $this->snack_payer,
            'booking_reference' => $this->booking_reference,
            'seats' => $this->seats,
        ];
    }
}
