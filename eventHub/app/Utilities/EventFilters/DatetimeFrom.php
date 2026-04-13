<?php

namespace App\Utilities\EventFilters;

use App\Utilities\FilterContract;
use App\Utilities\QueryFilter;

class DatetimeFrom extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('datetime_from', '>=', $value);
    }
}
