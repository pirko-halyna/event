<?php

namespace Tests\Feature\Action\EventTicketType;

use App\Models\Event;
use App\Models\EventTicketType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('action')]
#[Group('ticket-type')]
class ListTicketTypesActionTest extends TestCase
{
    #[Test]
    public function can_list_ticket_types_for_event(): void
    {
        $event = Event::factory()->create();
        EventTicketType::factory()->count(3)->create(['event_id' => $event->id]);

        $response = $this->getJson(route('events.ticket-types.index', $event));

        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    #[Test]
    public function only_returns_ticket_types_belonging_to_given_event(): void
    {
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        EventTicketType::factory()->count(2)->create(['event_id' => $event1->id]);
        EventTicketType::factory()->count(3)->create(['event_id' => $event2->id]);

        $response = $this->getJson(route('events.ticket-types.index', $event1));

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    #[Test]
    public function returns_empty_list_when_event_has_no_ticket_types(): void
    {
        $event = Event::factory()->create();

        $response = $this->getJson(route('events.ticket-types.index', $event));

        $response->assertStatus(200);
        $this->assertCount(0, $response->json());
    }

    #[Test]
    public function returns_404_for_non_existent_event(): void
    {
        $response = $this->getJson('/v1/events/99999/ticket-types');

        $response->assertStatus(404);
    }
}
