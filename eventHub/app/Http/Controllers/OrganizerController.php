<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizerController extends Controller
{
    /**
     * Get paginated organizers list
     *
     * @param PaginationRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        $paginated = Organizer::paginate($request->query('per_page', 10));

        return OrganizerResource::collection($paginated);
    }

    /**
     * Get organizer by id
     *
     * @param Organizer $organizer
     * @return Organizer
     */
    public function show(Organizer $organizer): Organizer
    {
        return $organizer;
    }
}
