<?php

namespace Tests\Feature\Request\EventTicketType;

use App\Models\Event;
use App\Models\EventTicketType;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('request')]
#[Group('ticket-type')]
class UpdateEventTicketTypeRequestTest extends TestCase
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
    public function name_cannot_be_empty_string_when_provided(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, $this->ticketType]), [
                'name' => '',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function name_cannot_exceed_255_characters_when_provided(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, $this->ticketType]), [
                'name' => str_repeat('a', 256),
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function price_must_be_at_least_zero_when_provided(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, $this->ticketType]), [
                'price' => -1,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['price']);
    }

    #[Test]
    public function quantity_must_be_at_least_zero_when_provided(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, $this->ticketType]), [
                'quantity' => -1,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['quantity']);
    }

    #[Test]
    public function quantity_must_be_integer_when_provided(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.ticket-types.update', [$this->event, $this->ticketType]), [
                'quantity' => 'not-an-integer',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['quantity']);
    }
}
