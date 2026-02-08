<?php

namespace App\Services;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;

class CalendarImportService
{
    public function __construct(protected TmdbService $tmdbService) {}

    public function import(): void
    {
        // Get the Service Account Email to filter for invites
        $serviceAccountEmail = config('google-calendar.auth_profiles.service_account.credentials_json.client_email');

        if (empty($serviceAccountEmail)) {
            echo "Error: Service Account Email not found. Check GOOGLE_CALENDAR_CREDENTIALS_B64 in .env.\n";
            return;
        }

        // Fetch events from the configured User Calendar ID
        $calendarId = config('google-calendar.calendar_id');

        if (empty($calendarId)) {
            echo "Error: Calendar ID not found. Check GOOGLE_CALENDAR_ID in .env.\n";
            return;
        }

        // Use the 'q' parameter to filter by the Service Account Email on the server side.
        // This significantly reduces data transfer by only getting events matching the email.
        try {
            $events = Event::get(Carbon::now()->subYears(10), Carbon::now()->addYear(), ['q' => $serviceAccountEmail], $calendarId);
        } catch (\Exception $e) {
            echo "Error fetching events: " . $e->getMessage() . "\n";
            return;
        }

        foreach ($events as $event) {
            // FILTER: duplicate check for processing loop
            $attendees = $event->attendees ?? [];
            $isInvited = collect($attendees)->contains(function ($attendee) use ($serviceAccountEmail) {
                return $attendee->email === $serviceAccountEmail;
            });

            if (!$isInvited) {
                continue;
            }

            // IDEMPOTENCY CHECK:
            // Check if we have already imported this specific Google Event ID.
            $existingShowing = Showing::with(['movie', 'cinema'])->where('google_event_id', $event->id)->first();

            if ($existingShowing) {
                // If it exists and has valid data, skip it.
                // If it is "Unknown", we want to re-process it to try and fix it.
                $isUnknown = ($existingShowing->movie && $existingShowing->movie->title === 'Unknown Movie') ||
                    ($existingShowing->cinema && $existingShowing->cinema->name === 'Unknown Cinema');

                if (!$isUnknown) {
                    continue;
                }
                echo "Re-processing 'Unknown' event: " . ($event->summary ?? 'Unknown') . "\n";
            } else {
                echo "Processing: " . ($event->summary ?? 'Unknown') . "\n";
            }

            $title = $event->summary ?? 'Unknown Title';
            $location = $event->location ?? 'Unknown Location';
            $description = $event->description ?? '';

            // Use LLM to parse the description
            $parser = app(EventParser::class);
            try {
                $parsedData = $parser->parse($title, $location, $description);
            } catch (\Exception $e) {
                echo "Error parsing description for event '{$title}': " . $e->getMessage() . "\n";
                // Fallback to empty array to allow partial import or skip?
                // For now, let's treat it as empty data and rely on defaults/nulls
                $parsedData = [];
            }

            if (!is_array($parsedData)) {
                $parsedData = [];
            }

            // Map names to User IDs (Alex = 1, Friend = 2)
            $ticketPayerId = $this->resolveUser($parsedData['ticket_payer'] ?? null);
            // Use snack_payer for both popcorn and soda
            $snackPayerId = $this->resolveUser($parsedData['snack_payer'] ?? null);

            // 5. DATA PERSISTENCE
            // Use firstOrCreate to avoid duplicating Movies and Cinemas.
            $movie = Movie::firstOrCreate(['title' => $parsedData['movie'] ?? 'Unknown Movie']);
            $cinema = Cinema::firstOrCreate(['name' => $parsedData['cinema'] ?? 'Unknown Cinema']);

            // Fetch metadata if missing
            if (!$movie->runtime && $movie->title !== 'Unknown Movie') {
                $this->fetchMovieMetadata($movie);
            }

            Showing::updateOrCreate(
                ['google_event_id' => $event->id],
                [
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
                ]
            );
        }
    }

    private function fetchMovieMetadata(Movie $movie): void
    {
        try {
            $searchResult = $this->tmdbService->searchMovie($movie->title);
            if ($searchResult && isset($searchResult['id'])) {
                $details = $this->tmdbService->getMovieDetails($searchResult['id']);
                if ($details) {
                    $movie->update([
                        'tmdb_id' => $details['id'],
                        'runtime' => $details['runtime'] ?? null,
                    ]);
                    echo "Updated metadata for '{$movie->title}': " . ($details['runtime'] ?? '?') . " mins\n";
                }
            }
        } catch (\Exception $e) {
            echo "Error fetching TMDB metadata for '{$movie->title}': " . $e->getMessage() . "\n";
        }
    }

    private function resolveUser(?string $name): int
    {
        // 1. If name is explicit, map it
        if ($name) {
            if (stripos($name, 'Alex') !== false) {
                return 1;
            }
            if (stripos($name, 'Casper') !== false || stripos($name, 'Friend') !== false) {
                return 2;
            }
        }

        // 2. Fallback: Use the user marked as 'is_current_payer'
        $payer = User::where('is_current_payer', true)->first();

        // 3. Absolute fallback to ID 1 (Alex) if DB state is weird
        return $payer ? $payer->id : 1;
    }
}
