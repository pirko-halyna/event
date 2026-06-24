<?php

namespace App\Services;

use App\Models\EventTicketType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EventTicketTypeService
{
    public function listTicketTypes(int $eventId): Collection
    {
        return EventTicketType::where('event_id', $eventId)->get();
    }

    public function createTicketType(int $eventId, array $data): EventTicketType
    {
        return DB::transaction(fn () => EventTicketType::create([
            ...$data,
            'event_id' => $eventId,
        ]));
    }

    public function updateTicketType(int $eventId, int $id, array $data): EventTicketType
    {
        return DB::transaction(function () use ($eventId, $id, $data) {
            $ticketType = EventTicketType::where('event_id', $eventId)->findOrFail($id);
            $ticketType->update($data);

            return $ticketType->fresh();
        });
    }

    public function deleteTicketType(int $eventId, int $id): void
    {
        $ticketType = EventTicketType::where('event_id', $eventId)->findOrFail($id);
        $ticketType->delete();
    }
}
