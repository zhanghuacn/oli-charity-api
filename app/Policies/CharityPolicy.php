<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use function getPermissionsTeamId;

class CharityPolicy
{
    use HandlesAuthorization;

    public function checkCharity($user, $model): bool
    {
        return $model->charity_id == getPermissionsTeamId();
    }
}
