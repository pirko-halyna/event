<?php

namespace Tests\Feature\Response\EventTicketType;

use App\Models\Event;
use App\Models\EventTicketType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('response')]
#[Group('ticket-type')]
class ListTicketTypesResponseTest extends TestCase
{
    #[Test]
    public function list_ticket_types_returns_correct_structure(): void
    {
        $event = Event::factory()->create();
        EventTicketType::factory()->count(2)->create(['event_id' => $event->id]);

        $response = $this->getJson(route('events.ticket-types.index', $event));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            [
                'id',
                'event_id',
                'name',
                'description',
                'price',
                'quantity',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
