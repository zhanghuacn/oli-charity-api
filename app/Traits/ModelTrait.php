<?php

namespace App\Traits;

trait ModelTrait
{
    protected function modelFilter()
    {
        return config('eloquentfilter.namespace', 'App\\ModelFilters\\')
            . str_replace(__NAMESPACE__ . '\\', '', get_class($this)) . 'Filter';
    }
}
