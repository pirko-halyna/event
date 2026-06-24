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
class DeleteTicketTypeResponseTest extends TestCase
{
    #[Test]
    public function delete_ticket_type_returns_no_content(): void
    {
        $user       = User::factory()->create();
        $event      = Event::factory()->create();
        $ticketType = EventTicketType::factory()->create(['event_id' => $event->id]);

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.ticket-types.destroy', [$event, $ticketType]));

        $response->assertStatus(204)->assertNoContent();
    }

    #[Test]
    public function delete_returns_not_found_for_missing_ticket_type(): void
    {
        $user  = User::factory()->create();
        $event = Event::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.ticket-types.destroy', [$event, 99999]));

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Not found']);
    }
}
