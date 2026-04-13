<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
