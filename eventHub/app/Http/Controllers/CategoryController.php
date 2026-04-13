<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Get paginated and sorted by order categories list
     *
     * @param PaginationRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        $paginated = Category::orderBy('order')
            ->paginate($request->query('per_page', 10));

        return  CategoryResource::collection($paginated);
    }
}
