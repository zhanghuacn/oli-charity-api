<?php

namespace App\ModelFilters;

use App\Models\News;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Passport;

class NewsFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = ['newsable'];

    public function keyword($value): NewsFilter
    {
        return $this->where('title', 'like', $value . '%')
            ->orWhere('description', 'like', $value . '%');
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
        if (Passport::hasScope('place-charity')) {
            $this->whereHasMorph('newsable', News::class, function (Builder $query) {
                $query->where('id', '=', getPermissionsTeamId());
            });
        }
    }
}
