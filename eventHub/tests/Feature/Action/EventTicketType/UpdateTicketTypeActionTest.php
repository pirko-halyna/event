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
class UpdateTicketTypeActionTest extends TestCase
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
    public function can_update_ticket_type(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, $this->ticketType]), [
                'name'     => 'Updated VIP',
                'price'    => '149.99',
                'quantity' => 30,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('event_ticket_types', [
            'id'       => $this->ticketType->id,
            'name'     => 'Updated VIP',
            'quantity' => 30,
        ]);
    }

    #[Test]
    public function can_partially_update_ticket_type(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, $this->ticketType]), [
                'quantity' => 5,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('event_ticket_types', [
            'id'       => $this->ticketType->id,
            'name'     => $this->ticketType->name,
            'quantity' => 5,
        ]);
    }

    #[Test]
    public function cannot_update_ticket_type_belonging_to_different_event(): void
    {
        $otherEvent      = Event::factory()->create();
        $otherTicketType = EventTicketType::factory()->create(['event_id' => $otherEvent->id]);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, $otherTicketType]), [
                'name' => 'Should Fail',
            ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_for_missing_ticket_type(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, 99999]), [
                'name' => 'Should Fail',
            ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function requires_authentication(): void
    {
        $response = $this->putJson(
            route('events.ticket-types.update', [$this->event, $this->ticketType]),
            ['name' => 'Unauthorized']
        );

        $response->assertStatus(401);
    }
}
