<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TeamService
{
    public function inviteToTeam(Ticket $ticket, Group $team, callable $success = null): GroupInvite
    {
        $invite = new GroupInvite();
        $invite->type = GroupInvite::TYPE_INVITE;
        $invite->inviter_id = Auth::id();
        $invite->ticket()->associate($ticket);
        $invite->team()->associate($team);
        $invite->save();

        if (!is_null($success)) {
            $success($invite);
        }
        return $invite;
    }

    public function hasPendingInvite(Ticket $ticket, Group $team): bool
    {
        return GroupInvite::where(['ticket_id' => $ticket->id, 'team_id' => $team->id])->exists();
    }

    public function acceptInvite($invite)
    {
        $invite->ticket->attachTeam($invite->team);
        $invite->delete();
    }

    public function denyInvite($invite)
    {
        $invite->delete();
    }
}
