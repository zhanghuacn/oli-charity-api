<?php

namespace App\Notifications;

use App\Models\GroupInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvitePaid extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invite;

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
        return [
            'title' => $this->invite->group->activity->name . ' event team invitation',
            'content' => sprintf('%s invites you to join %s team', $this->invite->inviter->name, $this->invite->group->name),
            'activity_id' => $this->invite->group->id
        ];
    }
}
