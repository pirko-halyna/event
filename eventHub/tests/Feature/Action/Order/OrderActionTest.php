<?php

namespace Feature\Action\Order;

use Illuminate\Support\Facades\Event as EventFacade;
use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Class OrderActionTest
 */
#[Group('action')]
#[Group('order')]
class OrderActionTest extends TestCase
{
    #[Test]
    public function user_can_create_order()
    {
        EventFacade::fake();

        $user = User::factory()->create();
        $event = Event::factory()->create(['price' => 500]);

        $ticketCount = 3;
        $totalAmount = $ticketCount * $event->price;

        $payload = [
            'event_id' => $event->id,
            'ticket_count' => $ticketCount,
        ];

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('authenticateToken')
                ->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('orders.store'), $payload);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Order created successfully',
            ])
            ->assertJsonStructure([
                'order_id',
                'message'
            ]);

        $orderId = $response->json('order_id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'user_id' => $user->id,
            'total_amount' => $totalAmount,
        ]);

        $this->assertDatabaseCount('tickets', $ticketCount);
        $this->assertDatabaseHas('tickets', [
            'order_id' => $orderId,
            'event_id' => $event->id,
        ]);
    }
}
