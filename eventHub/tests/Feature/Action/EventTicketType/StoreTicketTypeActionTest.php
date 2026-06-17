<?php

namespace Tests\Feature\Action\EventTicketType;

use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('action')]
#[Group('ticket-type')]
class StoreTicketTypeActionTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $user        = User::factory()->create();
        $this->event = Event::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });
    }

    #[Test]
    public function can_create_ticket_type_for_event(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), [
                'name'        => 'VIP',
                'price'       => '99.99',
                'quantity'    => 50,
                'description' => 'VIP access with premium seating',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('event_ticket_types', [
            'event_id' => $this->event->id,
            'name'     => 'VIP',
            'quantity' => 50,
        ]);
    }

    #[Test]
    public function can_create_ticket_type_without_optional_description(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), [
                'name'     => 'General',
                'price'    => '0',
                'quantity' => 200,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('event_ticket_types', [
            'event_id'    => $this->event->id,
            'name'        => 'General',
            'description' => null,
        ]);
    }

    #[Test]
    public function requires_authentication(): void
    {
        $response = $this->postJson(route('events.ticket-types.store', $this->event), [
            'name'     => 'VIP',
            'price'    => '99.99',
            'quantity' => 50,
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function returns_404_for_non_existent_event(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson('/v1/events/99999/ticket-types', [
                'name'     => 'VIP',
                'price'    => '99.99',
                'quantity' => 50,
            ]);

        $response->assertStatus(404);
    }
}
