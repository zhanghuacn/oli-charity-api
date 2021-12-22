<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    public function view(Admin $current, Admin $admin): bool
    {
        return true;
    }

    public function create(Admin $current): bool
    {
        return true;
    }

    public function update(Admin $current, Admin $admin): bool
    {
        return true;
    }

    public function delete(Admin $current, Admin $admin): bool
    {
        return $admin->id != 1;
    }
}
