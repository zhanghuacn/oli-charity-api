<?php

namespace App\Notifications;

use App\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InviteToTeam extends Notification
{
    private TeamInvite $invite;

    public function __construct(TeamInvite $invite)
    {
        $this->invite = $invite;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'inviter_id' => $this->invite->inviter->id,
            'inviter_name' => $this->invite->inviter->name,
            'inviter_avatar' => $this->invite->inviter->avatar,
            'team_name' => $this->invite->team->name,
            'accept_token' => $this->invite->accept_token,
            'deny_token' => $this->invite->deny_token,
        ];
    }
}
