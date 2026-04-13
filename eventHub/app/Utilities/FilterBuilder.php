<?php

namespace App\Utilities;

class FilterBuilder
{
    protected $query;
    protected $filters;
    protected $namespace;

    public function __construct($query, $filters, $namespace)
    {
        $this->query = $query;
        $this->filters = $filters;
        $this->namespace = $namespace;
    }

    public function apply()
    {
        foreach ($this->filters as $name => $value) {
            //Capitalize the first letter and remove '_' of the name  of the filter
            $normailizedName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

            //append name to the provided namespace
            $class = $this->namespace . "\\{$normailizedName}";

            if (!class_exists($class)) {
                continue;
            }

            //check if value provided
            if (!empty($value)) {
                //instantiate the class with the query, and call handle() method with the $value as parameter
                (new $class($this->query))->handle($value);
            } else {
                //instantiate the class with the query, and call handle() method without parameter (for sorting etc.)
                (new $class($this->query))->handle();
            }
        }

        return $this->query;
    }
}
