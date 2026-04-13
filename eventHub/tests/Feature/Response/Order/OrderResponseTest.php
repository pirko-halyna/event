<?php

namespace Feature\Response\Order;

use Illuminate\Support\Facades\Event as EventFacade;
use App\Models\Event;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('response')]
#[Group('order')]
class OrderResponseTest extends TestCase
{
    #[Test]
    public function create_order_success()
    {
        EventFacade::fake();

        $user = User::factory()->create();
        $event = Event::factory()->create();

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson(route('orders.store', ['event_id' => $event->id, 'ticket_count' => 1]));

        $response->assertCreated()
            ->assertJson(['message' => 'Order created successfully'])
            ->assertJsonStructure([
                'message',
                'order_id',
            ]);
    }
}
