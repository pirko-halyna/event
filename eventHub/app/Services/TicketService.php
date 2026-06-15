<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketService
{
    public function createTickets(int $orderId, int $count): void
    {
        $tickets = [];

        for ($i = 0; $i < $count; $i++) {
            $tickets[] = [
                'order_id' => $orderId,
                'ticket_code' => $this->generateTicketCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Ticket::insert($tickets);
    }

    protected function generateTicketCode(): string
    {
        return strtoupper(Str::random(10));
    }
}
