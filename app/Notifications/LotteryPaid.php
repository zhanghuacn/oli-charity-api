<?php

namespace App\Notifications;

use App\Models\Prize;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LotteryPaid extends Notification implements ShouldQueue
{
    use Queueable;

    private Prize $prize;

    public function __construct(Prize $prize)
    {
        $this->prize = $prize;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Congratulations on winning the prize：' . $this->prize->name,
            'content' => 'Please check it!',
        ];
    }
}
