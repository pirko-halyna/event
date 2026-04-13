<?php

namespace App\Utilities\EventFilters;

use App\Utilities\FilterContract;
use App\Utilities\QueryFilter;

class IsOnline extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('is_online', $value);
    }
}
