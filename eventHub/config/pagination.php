<?php

return [
    'max_per_page'     => (int) env('PAGINATION_MAX_PER_PAGE', 100),
    'default_per_page' => (int) env('PAGINATION_DEFAULT_PER_PAGE', 15),
];
