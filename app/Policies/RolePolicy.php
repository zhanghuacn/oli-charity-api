<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function view(Admin $admin, Role $role): bool
    {
        return Auth::getDefaultDriver() == $role->guard_name;
    }

    public function update(Admin $admin, Role $role): bool
    {
        return Auth::getDefaultDriver() == $role->guard_name;
    }

    public function delete(Admin $admin, Role $role): bool
    {
        return Auth::getDefaultDriver() == $role->guard_name;
    }
}
