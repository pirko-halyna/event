<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    /**
     * Get paginated event list
     *
     * @param IndexEventRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexEventRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $paginated = Event::with(['author', 'category', 'location', 'organizer'])
            ->filterBy($validated)
            ->paginate($request->query('per_page', 10));

        return EventResource::collection($paginated);
    }

    /**
     * Get event by id
     *
     * @param Event $event
     * return EventResource
     */
    public function show(Event $event): EventResource
    {
        $event->load(['author', 'category', 'location', 'organizer']);
        return new EventResource($event);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = Event::create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
        ]);

        return (new EventResource($event))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateEventRequest $request, Event $event): EventResource
    {
        $event->update($request->validated());

        return new EventResource($event);
    }

    public function destroy(Event $event): Response
    {
        $event->delete();

        return response()->noContent();
    }

    /**
     * Add event to favourite
     *
     * @param IndexEventRequest $request
     * @param Event $event
     * @return JsonResponse
     */
    public function addToFavourite(Request $request, Event $event): JsonResponse
    {
        Favourite::firstOrCreate([
            'user_id'  => $request->user()->id,
            'event_id' => $event->id,
        ]);

        return response()->json(['message' => 'Event added to favourite'], 200);
    }

    /**
     * Removing an event from favourite
     *
     * @param Request $request
     * @param Event $event
     * @return JsonResponse
     */
    public function removeFromFavourite(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        $user->favouriteEvents()->detach($event->id);

        return response()->json(['message' => 'Event removed from favourite list!'], 200);
    }
}
