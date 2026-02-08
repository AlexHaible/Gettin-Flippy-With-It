<?php

namespace Tests\Feature;

use App\Models\Showing;
use App\Models\User;
use App\Services\CalendarImportService;
use App\Services\EventParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use App\Services\TmdbService;
use Illuminate\Support\Facades\Log;

class CalendarImportServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_it_imports_events_and_saves_structured_data()
    {
        // 1. Mock Config
        config(['google-calendar.auth_profiles.service_account.credentials_json.client_email' => 'service@example.com']);
        config(['google-calendar.calendar_id' => 'primary']);

        // Mock Log
        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('error')->andReturnNull();

        // 2. Mock EventParser
        $mockParser = Mockery::mock(EventParser::class);
        $mockParser->shouldReceive('parse')->andReturn([
            'movie' => 'Mock Movie',
            'cinema' => 'Mock Cinema',
            'hall' => 'Hall 1',
            'price' => 200,
            'ticket_payer' => 'Alex',
            'snack_payer' => 'Casper',
            'booking_reference' => 'REF123',
            'seats' => 'A1, A2'
        ]);

        $this->app->instance(EventParser::class , $mockParser);

        // 3. Mock the implementation of the Event class
        // We use an external mock to intercept the static call `Event::get`
        $eventMock = Mockery::mock('overload:Spatie\GoogleCalendar\Event');

        $eventData = new \stdClass();
        $eventData->id = 'google_id_123';
        $eventData->summary = 'Mock Movie at Mock Cinema';
        $eventData->location = 'Mock Cinema';
        $eventData->description = 'Description';
        $eventData->startDateTime = now();
        $eventData->attendees = [(object)['email' => 'service@example.com']];

        // When iterating, the service accesses properties. 
        // If the service uses magic getters on the real class, we need to ensure our mock or object supports them.
        // The service accesses properties directly: $event->summary. 
        // stdClass supports this perfectly.

        $eventMock->shouldReceive('get')
            ->andReturn(collect([$eventData]));

        // 4. Create Users
        User::factory()->create(['username' => 'Alex', 'id' => 1]); // ID 1
        User::factory()->create(['username' => 'Casper', 'id' => 2]); // ID 2

        // 5. Run Import
        $tmdbMock = Mockery::mock(TmdbService::class);
        $tmdbMock->shouldReceive('searchMovie')->andReturn([]);

        $service = new CalendarImportService($tmdbMock);
        $service->import();

        // 6. Assert Database persistence
        $this->assertDatabaseHas('showings', [
            'booking_reference' => 'REF123',
            'seat_numbers' => 'A1, A2',
            'google_event_id' => 'google_id_123',
        ]);

        $this->assertDatabaseHas('movies', ['title' => 'Mock Movie']);
        $this->assertDatabaseHas('cinemas', ['name' => 'Mock Cinema']);
    }
}