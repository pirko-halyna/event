<?php

namespace App\Utilities\EventFilters;

use App\Utilities\FilterContract;
use App\Utilities\QueryFilter;

class Categories extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        if (is_array($value)) {
            $this->query->whereIn('category_id', $value);
        }
    }
}
