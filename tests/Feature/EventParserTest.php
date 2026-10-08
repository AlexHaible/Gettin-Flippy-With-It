<?php

namespace Tests\Feature;

use Anthropic\Messages\Message;
use Anthropic\Messages\TextBlock;
use Anthropic\ServiceContracts\MessagesContract;
use App\Services\EventParser;
use App\Services\EventParser\ShowingDetails;
use Mockery;
use Tests\TestCase;

class EventParserTest extends TestCase
{
    private function message(?ShowingDetails $parsed, string $stopReason = 'end_turn'): Message
    {
        $block = TextBlock::with(citations: null, text: '{}');
        $block->parsed = $parsed;

        return Message::with(
            id: 'msg_test',
            container: null,
            content: [$block],
            diagnostics: null,
            model: 'claude-opus-5-5',
            stopDetails: null,
            stopReason: $stopReason,
            stopSequence: null,
            usage: ['input_tokens' => 10, 'output_tokens' => 10],
        );
    }

    public function test_it_can_parse_event_description(): void
    {
        $details = new ShowingDetails;
        $details->movie = 'Inception';
        $details->cinema = 'Imperial';
        $details->hall = 'Bio 1';
        $details->price = 150;
        $details->booking_reference = 'REF123';
        $details->seats = 'A1, A2';

        $messages = Mockery::mock(MessagesContract::class);
        $messages->shouldReceive('create')->once()->andReturn($this->message($details));

        $result = (new EventParser($messages))->parse('Inception', 'Cinema City', 'Some dummy description');

        $this->assertEquals('Inception', $result['movie']);
        $this->assertEquals(150, $result['price']);
        // The parser returns what the LLM gave it; the service layer handles fallbacks.
        $this->assertNull($result['ticket_payer']);
        $this->assertNull($result['snack_payer']);
    }

    public function test_it_returns_empty_array_on_refusal(): void
    {
        $messages = Mockery::mock(MessagesContract::class);
        $messages->shouldReceive('create')->once()->andReturn($this->message(null, 'refusal'));

        $this->assertSame([], (new EventParser($messages))->parse('x', 'y', 'z'));
    }

    public function test_it_returns_empty_array_when_output_is_not_parsed(): void
    {
        $messages = Mockery::mock(MessagesContract::class);
        $messages->shouldReceive('create')->once()->andReturn($this->message(null));

        $this->assertSame([], (new EventParser($messages))->parse('x', 'y', 'z'));
    }
}
