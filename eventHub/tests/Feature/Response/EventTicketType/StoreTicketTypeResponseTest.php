<?php

namespace Tests\Feature\Response\EventTicketType;

use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('response')]
#[Group('ticket-type')]
class StoreTicketTypeResponseTest extends TestCase
{
    #[Test]
    public function store_ticket_type_returns_created_payload(): void
    {
        $user  = User::factory()->create();
        $event = Event::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.ticket-types.store', $event), [
                'name'        => 'Early Bird',
                'price'       => '29.99',
                'quantity'    => 100,
                'description' => 'Limited early bird tickets',
            ]);

        $response->assertStatus(201);
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
        $response->assertJsonFragment(['name' => 'Early Bird']);
        $response->assertJsonFragment(['quantity' => 100]);
        $response->assertJsonFragment(['event_id' => $event->id]);
    }
}
