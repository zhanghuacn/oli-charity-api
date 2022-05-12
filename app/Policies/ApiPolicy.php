<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\Apply;
use App\Models\Group;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ApiPolicy
{
    use HandlesAuthorization;

    public function apply(User $user, Activity $activity): bool
    {
        if ($activity->is_private) {
            if ($activity->applies()->where(['user_id' => $user->id, 'status' => Apply::STATUS_PASSED])->exists()) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function purchase(User $user, Activity $activity)
    {
        return optional($activity->my_ticket)->activity_id == $activity->id
            ? Response::allow() : Response::deny('You must buy event tickets.');
    }

    public function staff(User $user, Activity $activity): bool
    {
        return in_array(optional($activity->my_ticket)->type, [Ticket::TYPE_STAFF, Ticket::TYPE_HOST]);
    }

    public function group(User $user, Group $group): bool
    {
        return $group->tickets()->where('user_id', $user->id)->exists();
    }
}
