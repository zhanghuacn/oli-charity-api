<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function username($username): UserFilter
    {
        return $this->where('username', 'like', $username . '%');
    }

    public function email($email): UserFilter
    {
        return $this->where('email', 'like', $email . '%');
    }
}
