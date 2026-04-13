<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    /**
     * Get paginated locations list
     *
     * @param PaginationRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        $paginated = Location::paginate($request->per_page ?? 10);

        return LocationResource::collection($paginated);
    }
}
