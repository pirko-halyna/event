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

        $perPage = $request->get('per_page', 10);

        $favourites = $user->favouriteEvents()
            ->with(['author', 'category', 'location', 'organizer'])
            ->paginate($perPage);

        return EventWithFavouriteResource::collection($favourites);
    }
}
