<?php

namespace App\Services;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\GoogleCalendar\Event;

class CalendarImportService
{
    private const UNKNOWN_MOVIE = 'Unknown Movie';

    private const UNKNOWN_CINEMA = 'Unknown Cinema';

    public function __construct(
        protected TmdbService $tmdbService,
        protected EventParser $parser,
        protected WebhookNotifier $notifier,
    ) {}

    public function import(): void
    {
        Log::info('Starting Calendar Import...');

        // Get the Service Account Email to filter for invites
        $serviceAccountEmail = config('google-calendar.auth_profiles.service_account.credentials_json.client_email');

        if (empty($serviceAccountEmail)) {
            throw new Exception('Service Account Email not found. Check GOOGLE_CALENDAR_CREDENTIALS_B64 in .env.');
        }

        // Fetch events from the configured User Calendar ID
        $calendarId = config('google-calendar.calendar_id');

        if (empty($calendarId)) {
            throw new Exception('Calendar ID not found. Check GOOGLE_CALENDAR_ID in .env.');
        }

        Log::info('Using Service Account: '.$serviceAccountEmail);
        Log::info('Using Calendar ID: '.$calendarId);

        foreach ($this->fetchEvents($serviceAccountEmail, $calendarId) as $event) {
            if ($this->isInvited($event, $serviceAccountEmail)) {
                $this->importEvent($event);
            }
        }
    }

    /**
     * Fetch events from Google Calendar, using the 'q' parameter to filter by the
     * Service Account Email on the server side. This significantly reduces data
     * transfer by only getting events matching the email.
     */
    private function fetchEvents(string $serviceAccountEmail, string $calendarId): Collection
    {
        try {
            Log::info('Fetching events from Google Calendar (Last 10 years)...');
            $events = Event::get(now()->subYears(10), now()->addYear(), ['q' => $serviceAccountEmail], $calendarId);
        } catch (Exception $e) {
            Log::error('Error fetching events: '.$e->getMessage());
            throw new Exception('Error fetching events: '.$e->getMessage());
        }

        $count = count($events);
        Log::info('Fetched '.$count.' events from Google Calendar.');

        if ($count === 0) {
            Log::info("WARNING: No events found! Please ensure '{$serviceAccountEmail}' is added as an attendee to your movie events.");
        }

        return $events;
    }

    /**
     * @param  object  $event  A Google Calendar event.
     */
    private function isInvited(object $event, string $serviceAccountEmail): bool
    {
        return collect($event->attendees ?? [])
            ->contains(fn ($attendee) => $attendee->email === $serviceAccountEmail);
    }

    /**
     * @param  object  $event  A Google Calendar event.
     */
    private function importEvent(object $event): void
    {
        // IDEMPOTENCY CHECK:
        // Check if we have already imported this specific Google Event ID.
        $existingShowing = Showing::with(['movie', 'cinema'])->where('google_event_id', $event->id)->first();

        if ($existingShowing) {
            // If it exists and has valid data, skip it.
            // If it is "Unknown", we want to re-process it to try and fix it.
            $isUnknown = $this->isUnknown($existingShowing);

            $this->backfillMetadata($existingShowing);

            if (! $isUnknown) {
                return;
            }

            Log::info("Re-processing 'Unknown' event: ".($event->summary ?? 'Unknown'));
        } else {
            Log::info('Processing: '.($event->summary ?? 'Unknown'));
        }

        $showing = $this->persistShowing($event, $this->parseEvent($event));

        if ($showing->wasRecentlyCreated && $showing->start_time > now()) {
            $this->notifyNewShowing($showing);
        }
    }

    private function isUnknown(Showing $showing): bool
    {
        return ($showing->movie && $showing->movie->title === self::UNKNOWN_MOVIE)
            || ($showing->cinema && $showing->cinema->name === self::UNKNOWN_CINEMA);
    }

    /**
     * Fetch metadata (Runtime, Poster, Genres) for existing valid movies that are missing it.
     */
    private function backfillMetadata(Showing $showing): void
    {
        $movie = $showing->movie;

        if (! $movie || $movie->title === self::UNKNOWN_MOVIE) {
            return;
        }

        if (! $movie->runtime || ! $movie->poster_path || $movie->genres === null) {
            Log::info('Backfilling metadata for: '.$movie->title);
            $this->fetchMovieMetadata($movie);
        }
    }

    /**
     * Use the LLM to parse the event. On failure, fall back to an empty array and
     * rely on defaults/nulls so the event can still be partially imported.
     *
     * @param  object  $event  A Google Calendar event.
     * @return array<string, mixed>
     */
    private function parseEvent(object $event): array
    {
        $title = $event->summary ?? 'Unknown Title';
        $location = $event->location ?? 'Unknown Location';
        $description = $event->description ?? '';

        try {
            return $this->parser->parse($title, $location, $description);
        } catch (Exception $e) {
            Log::info("Error parsing description for event '{$title}': ".$e->getMessage());

            return [];
        }
    }

    /**
     * @param  object  $event  A Google Calendar event.
     * @param  array<string, mixed>  $parsedData
     */
    private function persistShowing(object $event, array $parsedData): Showing
    {
        $ticketPayerId = $this->resolveUser($parsedData['ticket_payer'] ?? null);
        // Use snack_payer for both popcorn and soda
        $snackPayerId = $this->resolveUser($parsedData['snack_payer'] ?? null);

        // Use firstOrCreate to avoid duplicating Movies and Cinemas.
        $movie = Movie::firstOrCreate(['title' => $parsedData['movie'] ?? self::UNKNOWN_MOVIE]);
        $cinema = Cinema::firstOrCreate(['name' => $parsedData['cinema'] ?? self::UNKNOWN_CINEMA]);

        if ((! $movie->runtime || ! $movie->poster_path) && $movie->title !== self::UNKNOWN_MOVIE) {
            $this->fetchMovieMetadata($movie);
        }

        $showing = Showing::firstOrNew(['google_event_id' => $event->id]);
        $showing->fill([
            'user_id' => $ticketPayerId, // Main booker
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'start_time' => $event->startDateTime ?? $event->startDate,
            'price_total' => $parsedData['price'] ?? 0,
            'hall_name' => $parsedData['hall'] ?? null,
            'booking_reference' => $parsedData['booking_reference'] ?? null,
            'seat_numbers' => $parsedData['seats'] ?? null,
            'popcorn_payer_id' => $snackPayerId,
            'soda_payer_id' => $snackPayerId,
        ]);
        $showing->save();

        return $showing;
    }

    private function notifyNewShowing(Showing $showing): void
    {
        try {
            $payerName = User::find($showing->popcorn_payer_id)?->username ?? 'Unknown';

            $message = "🍿 *Popcorn Protocol*: New movie booked!\n";
            $message .= "*{$showing->movie->title}* at {$showing->cinema->name} on ".$showing->start_time->format('l, jS M Y H:i').".\n";
            $message .= "It's *{$payerName}*'s turn to buy snacks!";

            $this->notifier->notify($message);
        } catch (Exception $e) {
            Log::info('Error dispatching webhooks: '.$e->getMessage());
        }
    }

    private function fetchMovieMetadata(Movie $movie): void
    {
        try {
            $searchResult = $this->tmdbService->searchMovie($movie->title);

            if (! $searchResult || ! isset($searchResult['id'])) {
                return;
            }

            $details = $this->tmdbService->getMovieDetails($searchResult['id']);

            if (! $details) {
                return;
            }

            $movie->update([
                'tmdb_id' => $details['id'],
                'runtime' => $details['runtime'] ?? null,
                'poster_path' => $details['poster_path'] ?? null,
                'backdrop_path' => $details['backdrop_path'] ?? null,
                'genres' => collect($details['genres'] ?? [])->pluck('name')->all(),
                'director' => collect($details['credits']['crew'] ?? [])->firstWhere('job', 'Director')['name'] ?? null,
                'cast' => collect($details['credits']['cast'] ?? [])->take(3)->pluck('name')->all(),
            ]);

            Log::info("Updated metadata for '{$movie->title}': ".($details['runtime'] ?? '?').' mins, poster, backdrop, genres, cast');
        } catch (Exception $e) {
            Log::info("Error fetching TMDB metadata for '{$movie->title}': ".$e->getMessage());
        }
    }

    /**
     * Map a parsed payer name to a user ID.
     */
    private function resolveUser(?string $name): int
    {
        // 1. If name is explicit, map it
        if ($name) {
            if (stripos($name, 'Alex') !== false) {
                return User::ALEX_ID;
            }

            if (stripos($name, 'Casper') !== false || stripos($name, 'Friend') !== false) {
                return User::CASPER_ID;
            }
        }

        // 2. Fallback: Use the user marked as 'is_current_payer'
        $payer = User::where('is_current_payer', true)->first();

        // 3. Absolute fallback to Alex if DB state is weird
        return $payer ? $payer->id : User::ALEX_ID;
    }
}
