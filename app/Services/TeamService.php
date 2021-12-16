<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TeamInvite;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TeamService
{
    public function inviteToTeam(Ticket $ticket, Team $team, callable $success = null): TeamInvite
    {
        $invite = new TeamInvite();
        $invite->type = TeamInvite::TYPE_INVITE;
        $invite->inviter_id = Auth::id();
        $invite->accept_token = md5(uniqid(microtime()));
        $invite->deny_token = md5(uniqid(microtime()));
        $invite->ticket()->associate($ticket);
        $invite->team()->associate($team);
        $invite->save();

        if (!is_null($success)) {
            $success($invite);
        }
        return $invite;
    }

    public function hasPendingInvite(Ticket $ticket, Team $team): bool
    {
        return TeamInvite::where(['ticket_id' => $ticket->id, 'team_id' => $team->id])->exists();
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
