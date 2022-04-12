<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class BazaarFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = ['user', 'charity', 'activity', 'goods', 'order'];

    public function user($id): BazaarFilter
    {
        return $this->where('user_id', '=', $id);
    }

    public function charity($id): BazaarFilter
    {
        return $this->where('charity_id', '=', $id);
    }

    public function activity($id): BazaarFilter
    {
        return $this->where('activity_id', '=', $id);
    }

    public function goods($id): BazaarFilter
    {
        return $this->where('goods_id', '=', $id);
    }

    public function order($id): BazaarFilter
    {
        return $this->where('order_id', '=', $id);
    }

    public function sort($value)
    {
        switch ($value) {
            case 'ASC':
                $this->orderBy('id');
                break;
            default:
                $this->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                break;
        }
    }

    public function setup()
    {
        if (!$this->input('sort')) {
            $this->push('sort', 'default');
        }
    }
}
