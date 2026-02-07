<?php

namespace Tests\Feature;

use App\Services\EventParser;
use Gemini\Testing\ClientFake;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Tests\TestCase;

class EventParserTest extends TestCase
{
    public function test_it_can_parse_event_description()
    {
        $fakeStructure = [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'functionCall' => [
                                    'name' => 'extract_showing_data',
                                    'args' => [
                                        "movie" => "Inception",
                                        "cinema" => "Imperial",
                                        "hall" => "Bio 1",
                                        "price" => 150,
                                        "ticket_payer" => null,
                                        "snack_payer" => null,
                                        "booking_reference" => "REF123",
                                        "seats" => "A1, A2"
                                    ]
                                ]
                            ]
                        ],
                        'role' => 'model'
                    ],
                    'finishReason' => 'STOP',
                    'index' => 0,
                    'safetyRatings' => []
                ]
            ],
            'usageMetadata' => [
                'promptTokenCount' => 10,
                'candidatesTokenCount' => 10,
                'totalTokenCount' => 20
            ]
        ];

        $response = GenerateContentResponse::from($fakeStructure);
        $fakeClient = new ClientFake([$response]);

        $parser = new EventParser($fakeClient);
        $result = $parser->parse("Inception", "Cinema City", "Some dummy description");

        $this->assertEquals("Inception", $result['movie']);
        // We expect NULL here now, because the parser should return what the LLM gave it.
        // The Service layer will handle the fallback.
        $this->assertNull($result['ticket_payer']);
        $this->assertNull($result['snack_payer']);
    }
}