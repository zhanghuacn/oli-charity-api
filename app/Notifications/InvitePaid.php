<?php

namespace App\Notifications;

use App\Models\GroupInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvitePaid extends Notification
{
    use Queueable;

    private GroupInvite $invite;

    public function __construct(GroupInvite $invite)
    {
        $this->invite = $invite;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
//        return [
//            'inviter_id' => $this->invite->inviter->id,
//            'inviter_name' => $this->invite->inviter->name,
//            'inviter_avatar' => $this->invite->inviter->avatar,
//            'team_name' => $this->invite->team->name,
//            'accept_token' => $this->invite->accept_token,
//            'deny_token' => $this->invite->deny_token,
//        ];
        return [
            'title' => '123',
            'content' => '321',
        ];
    }
}