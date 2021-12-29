<?php

namespace App\Policies\Admin;

use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class PermissionPolicy
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

    public function view(Admin $admin, Permission $permission): bool
    {
        return Auth::getDefaultDriver() == $permission->guard_name;
    }

    public function update(Admin $admin, Permission $permission): bool
    {
        return Auth::getDefaultDriver() == $permission->guard_name;
    }

    public function delete(Admin $admin, Permission $permission): bool
    {
        return Auth::getDefaultDriver() == $permission->guard_name;
    }
}
