<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventTicketTypeRequest;
use App\Http\Requests\UpdateEventTicketTypeRequest;
use App\Http\Resources\EventTicketTypeResource;
use App\Models\Event;
use App\Models\EventTicketType;
use App\Services\EventTicketTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EventTicketTypeController extends Controller
{
    public function __construct(
        protected EventTicketTypeService $service
    ) {}

    public function index(Event $event): AnonymousResourceCollection
    {
        return EventTicketTypeResource::collection(
            $this->service->listTicketTypes($event->id)
        );
    }

    public function store(CreateEventTicketTypeRequest $request, Event $event): JsonResponse
    {
        $ticketType = $this->service->createTicketType($event->id, $request->validated());

        return (new EventTicketTypeResource($ticketType))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateEventTicketTypeRequest $request, Event $event, EventTicketType $ticketType): EventTicketTypeResource
    {
        $updated = $this->service->updateTicketType($event->id, $ticketType->id, $request->validated());

        return new EventTicketTypeResource($updated);
    }

    public function destroy(Event $event, EventTicketType $ticketType): Response
    {
        $this->service->deleteTicketType($event->id, $ticketType->id);

        return response()->noContent();
    }
}
