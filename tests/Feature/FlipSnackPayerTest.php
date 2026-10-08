<?php

namespace Tests\Feature;

use App\Actions\FlipSnackPayer;
use App\Livewire\PayerControl;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FlipSnackPayerTest extends TestCase
{
    use RefreshDatabase;

    private int $eventCounter = 0;

    public function test_it_attributes_the_closest_showing_to_the_previous_payer_and_flips_the_payer(): void
    {
        $alex = User::factory()->create(['is_current_payer' => true]);
        $casper = User::factory()->create(['is_current_payer' => false]);

        $past = $this->showing($alex, now()->subDays(3));
        $closest = $this->showing($alex, now()->addHours(2));
        $farFuture = $this->showing($alex, now()->addDays(10));

        $newPayer = app(FlipSnackPayer::class)($casper);

        $this->assertTrue($newPayer->is($casper));

        $this->assertSame($alex->id, $closest->fresh()->popcorn_payer_id);
        $this->assertSame($alex->id, $closest->fresh()->soda_payer_id);
        $this->assertNull($past->fresh()->popcorn_payer_id);
        $this->assertNull($farFuture->fresh()->popcorn_payer_id);

        $this->assertFalse((bool) $alex->fresh()->is_current_payer);
        $this->assertTrue((bool) $casper->fresh()->is_current_payer);
    }

    public function test_it_picks_the_past_showing_when_that_is_closer(): void
    {
        $alex = User::factory()->create(['is_current_payer' => true]);
        $casper = User::factory()->create(['is_current_payer' => false]);

        $justEnded = $this->showing($alex, now()->subHour());
        $nextWeek = $this->showing($alex, now()->addWeek());

        app(FlipSnackPayer::class)($casper);

        $this->assertSame($alex->id, $justEnded->fresh()->popcorn_payer_id);
        $this->assertNull($nextWeek->fresh()->popcorn_payer_id);
    }

    public function test_it_is_a_no_op_when_the_user_already_is_the_payer(): void
    {
        $alex = User::factory()->create(['is_current_payer' => true]);
        User::factory()->create(['is_current_payer' => false]);
        $showing = $this->showing($alex, now()->addHour());

        $result = app(FlipSnackPayer::class)($alex);

        $this->assertNull($result);
        $this->assertNull($showing->fresh()->popcorn_payer_id);
        $this->assertTrue((bool) $alex->fresh()->is_current_payer);
        $this->assertSame(1, User::where('is_current_payer', true)->count());
    }

    public function test_the_payer_control_component_flips_and_dispatches_events(): void
    {
        $alex = User::factory()->create(['is_current_payer' => true]);
        $casper = User::factory()->create(['is_current_payer' => false]);

        Livewire::actingAs($casper)
            ->test(PayerControl::class)
            ->assertSet('payerName', $alex->username)
            ->call('flip')
            ->assertSet('payerName', $casper->username)
            ->assertDispatched('turn-flipped');
    }

    private function showing(User $user, $startTime): Showing
    {
        return Showing::create([
            'user_id' => $user->id,
            'movie_id' => Movie::create(['title' => 'Movie '.++$this->eventCounter])->id,
            'cinema_id' => Cinema::firstOrCreate(['name' => 'Test Cinema'])->id,
            'start_time' => $startTime,
            'google_event_id' => 'event-'.$this->eventCounter,
        ]);
    }
}
