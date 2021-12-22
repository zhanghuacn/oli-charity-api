<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Auth;

class PermissionFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function guard($guard): PermissionFilter
    {
        return $this->where('guard_name', '=', $guard);
    }

    public function setup()
    {
        if (Auth::check()) {
            $this->push('guard', Auth::getDefaultDriver());
        }
    }
}
