<?php

namespace App\Policies\Admin;

use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny($model): bool
    {
        return true;
    }

    public function create($model): bool
    {
        return true;
    }

    public function view($model, Role $role): bool
    {
        return Auth::getDefaultDriver() == $role->guard_name;
    }

    public function update($model, Role $role): bool
    {
        return Auth::getDefaultDriver() == $role->guard_name;
    }

    public function delete($model, Role $role): bool
    {
        return Auth::getDefaultDriver() == $role->guard_name &&
            $role->name != Role::ROLE_ADMIN_SUPER_ADMIN;
    }
}
