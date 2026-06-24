<?php

namespace Tests\Feature\Response\EventTicketType;

use App\Models\Event;
use App\Models\EventTicketType;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('response')]
#[Group('ticket-type')]
class UpdateTicketTypeResponseTest extends TestCase
{
    #[Test]
    public function update_ticket_type_returns_updated_payload(): void
    {
        $user       = User::factory()->create();
        $event      = Event::factory()->create();
        $ticketType = EventTicketType::factory()->create(['event_id' => $event->id]);

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$event, $ticketType]), [
                'name'     => 'Updated Name',
                'quantity' => 75,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'event_id',
            'name',
            'description',
            'price',
            'quantity',
            'created_at',
            'updated_at',
        ]);
        $response->assertJsonFragment(['name' => 'Updated Name']);
        $response->assertJsonFragment(['quantity' => 75]);
    }
}
