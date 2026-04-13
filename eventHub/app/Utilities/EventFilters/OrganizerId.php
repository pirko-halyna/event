<?php

namespace App\Utilities\EventFilters;

use App\Utilities\FilterContract;
use App\Utilities\QueryFilter;

class OrganizerId extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('organizer_id', $value);
    }
}
