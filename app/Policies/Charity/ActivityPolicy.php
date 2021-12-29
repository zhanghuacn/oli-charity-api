<?php

namespace App\Policies\Charity;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Activity $activity): bool
    {
        return $activity->charity_id == getPermissionsTeamId();
    }

    public function update(User $user, Activity $activity): bool
    {
        return $activity->charity_id == getPermissionsTeamId();
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $activity->charity_id == getPermissionsTeamId();
    }
}
