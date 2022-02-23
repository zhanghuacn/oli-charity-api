<?php

namespace App\ModelFilters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'CURRENT' => $this->where('begin_time', '<=', Carbon::tz(config('app.timezone'))->now())->where('end_time', '>=', Carbon::tz(config('app.timezone'))->now()),
            'NOT_CURRENT' => Auth::check() ? $this->whereNotExists(
                function ($query) {
                    $query->select(DB::raw(1))
                        ->from('tickets')
                        ->whereRaw('tickets.activity_id = activities.id AND tickets.user_id = ' . Auth::id() . '  AND activities.begin_time <= now() AND activities.end_time >= now()');
                }
            ) : null,
            'UPCOMING' => $this->where('begin_time', '>', Carbon::tz(config('app.timezone'))->now()),
            'ACTIVE' => $this->where('end_time', '>=', Carbon::tz(config('app.timezone'))->now()),
            'PAST' => $this->where('end_time', '<', Carbon::tz(config('app.timezone'))->now()),
            default => abort(400, 'Incorrect query criteria parameters.'),
        };
    }

    public function keyword($value): ?ActivityFilter
    {
        return $this->where('name', 'like', $value . '%')
            ->orWhere('description', 'like', $value . '%');
    }

    public function charity($id): ActivityFilter
    {
        return $this->where('charity_id', '=', $id);
    }

    public function isVisible($value): ActivityFilter
    {
        return $this->where('is_visible', '=', $value);
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
        if (Auth::check() && Auth::user()->tokenCan('place-charity')) {
            $this->push('charity', getPermissionsTeamId());
        }
    }
}
