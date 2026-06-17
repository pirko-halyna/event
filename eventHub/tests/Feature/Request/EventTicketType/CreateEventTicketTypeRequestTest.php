<?php

namespace Tests\Feature\Request\EventTicketType;

use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('request')]
#[Group('ticket-type')]
class CreateEventTicketTypeRequestTest extends TestCase
{
    private Event $event;
    private array $basePayload;

    protected function setUp(): void
    {
        parent::setUp();

        $user        = User::factory()->create();
        $this->event = Event::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $this->basePayload = [
            'name'     => 'VIP',
            'price'    => '99.99',
            'quantity' => 50,
        ];
    }

    #[Test]
    public function name_is_required(): void
    {
        $payload = $this->basePayload;
        unset($payload['name']);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function name_cannot_exceed_255_characters(): void
    {
        $payload = array_merge($this->basePayload, ['name' => str_repeat('a', 256)]);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function price_is_required(): void
    {
        $payload = $this->basePayload;
        unset($payload['price']);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['price']);
    }

    #[Test]
    public function price_must_be_at_least_zero(): void
    {
        $payload = array_merge($this->basePayload, ['price' => -1]);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['price']);
    }

    #[Test]
    public function quantity_is_required(): void
    {
        $payload = $this->basePayload;
        unset($payload['quantity']);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['quantity']);
    }

    #[Test]
    public function quantity_must_be_at_least_zero(): void
    {
        $payload = array_merge($this->basePayload, ['quantity' => -1]);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['quantity']);
    }

    #[Test]
    public function description_is_optional(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $this->event), $this->basePayload);

        $response->assertStatus(201);
    }
}
