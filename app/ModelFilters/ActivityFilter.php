<?php

namespace App\ModelFilters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;

class ActivityFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = ['tickets'];

    public function user($id): ActivityFilter
    {
        return $this->related('tickets', 'user_id', '=', $id);
    }

    public function filter($filter): ?ActivityFilter
    {
        return match ($filter) {
            'CURRENT' => $this->where('begin_time', '<=', Carbon::now())->where('end_time', '>=', Carbon::now()),
            'UPCOMING' => $this->where('begin_time', '>', Carbon::now()),
            'PAST' => $this->where('end_time', '<', Carbon::now()),
        };
    }
}
