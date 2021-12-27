<?php

namespace App\Traits;

trait ModelFilter
{
    protected function modelFilter(): string
    {
        return config('eloquentfilter.namespace', 'App\\ModelFilters\\')
            . str_replace('App\Models' . '\\', '', get_class($this)) . 'Filter';
    }
}
