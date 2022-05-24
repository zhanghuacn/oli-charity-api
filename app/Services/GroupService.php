<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class GroupService
{
    public function inviteToGroup(Ticket $ticket, Group $group, callable $success = null): GroupInvite
    {
        $invite = new GroupInvite();
        $invite->type = GroupInvite::TYPE_INVITE;
        $invite->inviter_id = Auth::id();
        $invite->ticket()->associate($ticket);
        $invite->group()->associate($group);
        $invite->save();

        if (!is_null($success)) {
            $success($invite);
        }
        return $invite;
    }

    public function hasPendingInvite(Ticket $ticket, Group $group): bool
    {
        return GroupInvite::where(['ticket_id' => $ticket->id, 'group_id' => $group->id])->exists();
    }

    public function acceptInvite($invite)
    {
        $invite->ticket->attachGroup($invite->group);
        $invite->delete();
    }

    public function denyInvite($invite)
    {
        $invite->delete();
    }
}
