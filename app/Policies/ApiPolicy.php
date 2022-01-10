<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\Apply;
use App\Models\Group;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiPolicy
{
    use HandlesAuthorization;

    public function apply(User $user, Activity $activity): bool
    {
        if ($activity->is_private == true) {
            if ($activity->applies()->where(['user_id' => $user->id, 'status' => Apply::STATUS_PASSED])->exists()) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function purchase(User $user, Activity $activity): bool
    {
        return in_array($activity->id, $user->tickets->pluck('activity_id')->toArray());
    }

    public function staff(User $user, Activity $activity): bool
    {
        return $activity->tickets()->where(['user_id' => $user->id, 'type' => Ticket::TYPE_STAFF])->doesntExist();
    }

    public function owner(User $user, Group $group): bool
    {
        return $user->id == $group->owner_id;
    }
}
