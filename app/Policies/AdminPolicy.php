<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class AdminPolicy
{
    use HandlesAuthorization;

    public function checkDriver($model): bool
    {
        return Auth::getDefaultDriver() == $model->guard_name;
    }
}
