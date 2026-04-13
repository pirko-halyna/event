<?php

namespace App\Utilities\EventFilters;

use App\Utilities\FilterContract;
use App\Utilities\QueryFilter;

class Search extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where(function ($query) use ($value) {
            $query->where('title', 'like', '%' . $value . '%')
                ->orWhere('description', 'like', '%' . $value . '%');
        });
    }
}
