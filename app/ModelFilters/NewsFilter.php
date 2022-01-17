<?php

namespace App\ModelFilters;

use App\Models\Admin;
use App\Models\Charity;
use App\Models\News;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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

    public function type($value)
    {
        switch ($value) {
            case 'CHARITY':
                $this->whereHasMorph('newsable', [Charity::class]);
                break;
            case 'SYSTEM':
                $this->whereHasMorph('newsable', [Admin::class]);
                break;
            default:
                $this->whereHasMorph('newsable', [Charity::class, Admin::class]);

        }
    }

    public function setup()
    {
        if (!$this->input('sort')) {
            $this->push('sort', 'default');
        }
        if (Auth::check() && Auth::user()->tokenCan('place-charity')) {
            $this->whereHasMorph('newsable', Charity::class, function (Builder $query) {
                $query->where('id', '=', getPermissionsTeamId());
            });
        }
    }
}
