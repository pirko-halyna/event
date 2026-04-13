<?php

namespace App\Utilities\EventFilters;

use App\Utilities\FilterContract;
use App\Utilities\QueryFilter;

class Locations extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        if (is_array($value)) {
            $this->query->whereIn('location_id', $value);
        }
    }
}
