<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CalendarImportService;
use App\Services\EventParser;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class CalendarImportServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @runInSeparateProcess
     *
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
            'seats' => 'A1, A2',
        ]);

        $this->app->instance(EventParser::class, $mockParser);

        // 3. Mock the implementation of the Event class
        // We use an external mock to intercept the static call `Event::get`
        $eventMock = Mockery::mock('overload:Spatie\GoogleCalendar\Event');

        $eventData = new \stdClass;
        $eventData->id = 'google_id_123';
        $eventData->summary = 'Mock Movie at Mock Cinema';
        $eventData->location = 'Mock Cinema';
        $eventData->description = 'Description';
        $eventData->startDateTime = now()->addDays(5);
        $eventData->attendees = [(object) ['email' => 'service@example.com']];

        // 6. Mock HTTP for webhook
        config(['app.discord_webhook_url' => 'http://discord.test']);
        config(['app.slack_webhook_url' => 'http://slack.test']);
        putenv('DISCORD_WEBHOOK_URL=http://discord.test');
        putenv('SLACK_WEBHOOK_URL=http://slack.test');

        Http::fake([
            'http://discord.test' => Http::response('ok', 200),
            'http://slack.test' => Http::response('ok', 200),
        ]);

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

        Http::assertSent(function ($request) {
            return $request->url() == 'http://discord.test' &&
                   str_contains($request['content'], 'Mock Movie');
        });

        Http::assertSent(function ($request) {
            return $request->url() == 'http://slack.test' &&
                   str_contains($request['text'], 'Mock Movie');
        });

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
