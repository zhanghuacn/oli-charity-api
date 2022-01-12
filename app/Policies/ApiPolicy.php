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
        return $activity->my_ticket->activity_id == $activity->id;
    }

    public function staff(User $user, Activity $activity): bool
    {
        return in_array($activity->my_ticket->type, [Ticket::TYPE_STAFF, Ticket::TYPE_HOST, Ticket::TYPE_CHARITY]);
    }

    public function owner(User $user, Group $group): bool
    {
        return $user->id == $group->owner_id;
    }
}
