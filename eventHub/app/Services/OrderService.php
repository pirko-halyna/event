<?php

namespace App\Services;

use App\Events\EventRegistered;
use App\Models\Event;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected TicketService $ticketService
    ) {
    }

    public function createOrderWithTickets(int $userId, int $eventId, int $ticketCount): Order
    {
        return DB::transaction(function () use ($userId, $eventId, $ticketCount) {
            $event = Event::findOrFail($eventId);

            $order = Order::create([
                'user_id' => $userId,
                'event_id' => $eventId,
                'total_amount' => $event->price * $ticketCount,
            ]);

            $this->ticketService->createTickets($order->id, $eventId, $ticketCount);

            event(new EventRegistered($order));

            return $order;
        });
    }
}
