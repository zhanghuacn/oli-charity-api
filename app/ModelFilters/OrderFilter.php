<?php

namespace App\ModelFilters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;

class OrderFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function charity($id): OrderFilter
    {
        return $this->where('charity_id', '=', $id);
    }

    public function type($type): OrderFilter
    {
        return $this->where('type', '=', $type);
    }

    public function activity($id): OrderFilter
    {
        return $this->where('activity_id', '=', $id);
    }

    public function user($id): OrderFilter
    {
        return $this->where('user_id', '=', $id);
    }

    public function paymentStatus($paymentStatus): OrderFilter
    {
        return $this->where('payment_status', '=', $paymentStatus);
    }

    public function year($year): OrderFilter
    {
        return $this->whereYear('payment_time', $year);
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
    }
}
