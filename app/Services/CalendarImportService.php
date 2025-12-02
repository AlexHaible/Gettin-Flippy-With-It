<?php

namespace app\Services;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Spatie\GoogleCalendar\Event;

class CalendarImportService {
    public function import(): void {
        // Fetch events. Because we use a Service Account, this only fetches
        // events where the Service Account email was explicitly invited.
        $events = Event::get();

        foreach ($events as $event) {
            // IDEMPOTENCY CHECK:
            // Skip processing if we have already imported this specific Google Event ID.
            if (Showing::where('google_event_id', $event->id)->exists()) {
                continue;
            }

            $description = $event->description;

            // 1. PARSE PRICE
            // Looks for "Din pris: 300" or "Din pris: 300,00"
            preg_match('/Din pris:\s*(\d+)([.,]\d+)?/', $description, $priceMatches);
            // Convert to integer (e.g. 300 becomes 300).
            // NOTE: If you want cents, multiply by 100 here. Assuming input is whole Kr.
            $price = isset($priceMatches[1]) ? (int) $priceMatches[1] : 0;

            // 2. PARSE REFERENCE CODE
            // Captures alphanumeric strings after "bookingreference er:"
            preg_match('/bookingreference er:\s*([A-Za-z0-9]+)/', $description, $refMatches);
            $ref = $refMatches[1] ?? null;

            // 3. PARSE MOVIE, CINEMA, AND HALL
            // This complex regex breaks down the line: "- {Title} Biograf: {Cinema}, {Hall}"
            // /mi flags make it multiline and case-insensitive.
            preg_match('/-\s+(.*?)\s+Biograf:\s+(.*?)[,\n\r]+\s*(Sal\s+\d+|IMAX|.*)/mi', $description, $infoMatches);
            $movieTitle = trim($infoMatches[1] ?? 'Unknown Movie');
            $cinemaName = trim($infoMatches[2] ?? 'Unknown Cinema');
            $hallName = trim($infoMatches[3] ?? 'Unknown Hall');

            // 4. PARSE SEATS FOR HEATMAP
            // Regex captures "C1, C2" or "C1,C2" or "Row 1, Row 2" (alphanumeric + comma + space)
            preg_match('/Dine sæder:\s*([A-Za-z0-9,\s]+)/', $description, $seatMatches);
            $rawSeats = $seatMatches[1] ?? null;

            // DATA NORMALIZATION:
            // We want the database to strictly store "C1,C2" (no spaces) to make downstream parsing easier.
            if ($rawSeats) {
                // Explode by comma to get individual entries
                $seatArray = explode(',', $rawSeats);
                // Trim whitespace from each seat (" C1 " -> "C1")
                $seatArray = array_map('trim', $seatArray);
                // Re-implode with just commas
                $normalizedSeats = implode(',', $seatArray);
            } else {
                $normalizedSeats = null;
            }

            // 5. DATA PERSISTENCE
            // Use firstOrCreate to avoid duplicating Movies and Cinemas.
            $movie = Movie::firstOrCreate(['title' => $movieTitle]);
            $cinema = Cinema::firstOrCreate(['name' => $cinemaName]);

            // Determine the booker based on the event creator.
            // Fallback to the first user in DB if creator email doesn't match our system.
            $booker = User::where('email', $event->creator->email)->first() ?? User::first();

            Showing::create([
                'user_id'           => $booker->id,
                'movie_id'          => $movie->id,
                'cinema_id'         => $cinema->id,
                'google_event_id'   => $event->id,
                'start_time'        => $event->startDateTime,
                'price_total'       => $price,
                'hall_name'         => $hallName,
                'booking_reference' => $ref,
                'seat_numbers'      => $normalizedSeats,
            ]);
        }
    }
}
