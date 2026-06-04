<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventWithFavouriteResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Get list of favourite events
     *
     * @param IndexEventRequest $request
     * @return AnonymousResourceCollection
     */
    public function getFavouriteEvents(IndexEventRequest $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $favourites = $user->favouriteEvents()
            ->with(['author', 'category', 'location', 'organizer'])
            ->withExists(['favouriteUsers as is_favourite' => fn($q) => $q->where('favourites.user_id', $user->id)])
            ->paginate($request->get('per_page', 10));

        return EventWithFavouriteResource::collection($favourites);
    }
}
