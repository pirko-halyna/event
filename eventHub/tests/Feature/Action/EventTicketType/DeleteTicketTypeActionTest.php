<?php

namespace Tests\Feature\Action\EventTicketType;

use App\Models\Event;
use App\Models\EventTicketType;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('action')]
#[Group('ticket-type')]
class DeleteTicketTypeActionTest extends TestCase
{
    private Event $event;
    private EventTicketType $ticketType;

    protected function setUp(): void
    {
        parent::setUp();

        $user             = User::factory()->create();
        $this->event      = Event::factory()->create();
        $this->ticketType = EventTicketType::factory()->create(['event_id' => $this->event->id]);

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });
    }

    #[Test]
    public function can_delete_ticket_type(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.ticket-types.destroy', [$this->event, $this->ticketType]));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('event_ticket_types', ['id' => $this->ticketType->id]);
    }

    #[Test]
    public function cannot_delete_ticket_type_belonging_to_different_event(): void
    {
        $otherEvent      = Event::factory()->create();
        $otherTicketType = EventTicketType::factory()->create(['event_id' => $otherEvent->id]);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.ticket-types.destroy', [$this->event, $otherTicketType]));

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_for_missing_ticket_type(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.ticket-types.destroy', [$this->event, 99999]));

        $response->assertStatus(404);
    }

    #[Test]
    public function requires_authentication(): void
    {
        $response = $this->deleteJson(
            route('events.ticket-types.destroy', [$this->event, $this->ticketType])
        );

        $response->assertStatus(401);
    }
}
