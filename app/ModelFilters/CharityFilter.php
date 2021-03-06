<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class CharityFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function keyword($value): ?CharityFilter
    {
        return $this->where('name', 'like', $value . '%')
            ->orWhere('description', 'like', $value . '%');
    }

    public function isVisible($value): CharityFilter
    {
        return $this->where('is_visible', '=', $value);
    }

    public function sort($value)
    {
        switch ($value) {
            case 'AMOUNT':
                $this->orderBy('cache->amount', 'desc');
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
