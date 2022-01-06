<?php

namespace App\ModelFilters;

use App\Models\Role;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

class RoleFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function guard($guard): RoleFilter
    {
        return $this->where('guard_name', '=', $guard);
    }

    public function team($team): RoleFilter
    {
        return $this->where('team_id', '=', $team);
    }

    public function name($name): RoleFilter
    {
        return $this->where('name', '=', $name);
    }

    public function setup()
    {
        if (Auth::check()) {
            $this->push('guard', Auth::getDefaultDriver());
            $this->push('team', getPermissionsTeamId());
            if (Auth::user()->tokenCan('place-charity')) {
                $this->where('name', '<>', Role::ROLE_CHARITY_SUPER_ADMIN);
            }
            if (Auth::user()->tokenCan('place-sponsor')) {
                $this->where('name', '<>', Role::ROLE_SPONSOR_SUPER_ADMIN);
            }
            if (Auth::user()->tokenCan('place-admin')) {
                $this->where('name', '<>', Role::ROLE_ADMIN_SUPER_ADMIN);
            }
        }
    }
}
