<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class TicketFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = ['user'];

    public function user($id): TicketFilter
    {
        return $this->where('user_id', '=', $id);
    }

    public function type($type): TicketFilter
    {
        return $this->where('type', '=', $type);
    }

    public function code($code): TicketFilter
    {
        return $this->where('code', '=', $code);
    }

    public function activityId($value): TicketFilter
    {
        return $this->where('activity_id', '=', $value);
    }

    public function phone($phone): TicketFilter
    {
        return $this->whereHas('user', function (Builder $query) use ($phone) {
            $query->where('phone', 'like', $phone . '%');
        });
    }

    public function email($email): TicketFilter
    {
        return $this->whereHas('user', function (Builder $query) use ($email) {
            $query->where('email', 'like', $email . '%');
        });
    }

    public function name($name): Builder|TicketFilter
    {
        return $this->whereHas('user', function (Builder $query) use ($name) {
            $query->where('name', 'like', $name . '%')
                ->orWhere('username', 'like', $name . '%')
                ->orWhere('email', 'like', $name . '%');
        });
    }

    public function filter($filter): TicketFilter
    {
        return match ($filter) {
            'COMPLETED' => $this->whereNotNull('verified_at'),
            'INCOMPLETE' => $this->whereNull('verified_at'),
            default => abort(400, 'Incorrect query criteria parameters.'),
        };
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
