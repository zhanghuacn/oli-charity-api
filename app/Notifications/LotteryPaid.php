<?php

namespace App\Notifications;

use App\Models\Prize;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\AwsSns\SnsChannel;
use NotificationChannels\AwsSns\SnsMessage;

class LotteryPaid extends Notification implements ShouldQueue
{
    use Queueable;

    private Prize $prize;
    private User $user;

    public function __construct(Prize $prize, User $user)
    {
        $this->prize = $prize;
        $this->user = $user;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail', SnsChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        $event = $this->prize->activity->name;
        $prize = $this->prize->name;
        $date = Carbon::parse($this->prize->activity->end_time)->toFormattedDateString();
        return [
            'title' => "Congratulations",
            'content' => "You've won the $prize in our $event , You can claim your prize on the day of the banquet on $date. ",
            'activity_id' => $this->prize->activity_id,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())->subject(sprintf('%s Congratulations！', config('app.name')))
            ->markdown('emails.award', [
                'name' => empty($this->user->first_name) ? $this->user->name : sprintf('%s %s', $this->user->first_name, $this->user->last_name),
                'prize' => $this->prize->name,
                'event' => $this->prize->activity->name,
                'image' => collect($this->prize->images)->first(),
                'url' => sprintf('%s/events/lottery/result/%d?eventsId=%d', config('services.custom.app_spa_url'), $this->prize->lottery_id, $this->prize->activity_id),
            ]);
    }

    public function toSns($notifiable): SnsMessage
    {
        $event = $this->prize->activity->name;
        $prize = $this->prize->name;
        $name = empty($this->user->first_name) ? $this->user->name : sprintf('%s %s', $this->user->first_name, $this->user->last_name);
        $date = Carbon::parse($this->prize->activity->end_time)->toFormattedDateString();
        $message = <<<EOF
Dear $name
Congratulations, you've won the $prize in our $event,
You can claim your prize on the day of the banquet on $date.
If you have any questions, please contact the administrator of the WeChat group and check the details by email.
EOF;
        Log::info($message);
        return SnsMessage::create()
            ->body($message)
            ->transactional()
            ->sender(config('app.name'));
    }
}
