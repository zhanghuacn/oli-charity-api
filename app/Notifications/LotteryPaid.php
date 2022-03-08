<?php

namespace App\Notifications;

use App\Models\Prize;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use JetBrains\PhpStorm\ArrayShape;
use NotificationChannels\AwsSns\SnsChannel;
use NotificationChannels\AwsSns\SnsMessage;

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
        return ['database', 'mail', SnsChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Winning the ' . $this->prize->activity->name . ' event',
            'content' => 'Congratulations on winning the prize：' . $this->prize->name,
            'activity_id' => $this->prize->activity_id,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())->subject('Imagine 2080 Congratulations！')->view(
            'emails.SendWinnerPrize',
            ['prize' => $this->prize->name, 'event' => $this->prize->activity->name, 'image' => collect($this->prize->images)->first()]
        );
    }

    public function toSns($notifiable): SnsMessage
    {
        $event = $this->prize->activity->name;
        $prize = $this->prize->name;
        $date = Carbon::parse($this->prize->activity->end_time)->tz(config('app.timezone'))->toFormattedDateString();
        $message = <<<EOF
Dear user
Congratulations, you've won the $prize in our $event,
You can claim your prize on the day of the banquet on $date.
If you have any questions, please contact the administrator of the WeChat group and check the details by email.
EOF;

        return SnsMessage::create([
            'body' => $message,
            'promotional' => true,
            'sender' => 'Imagine2080',
        ]);
    }
}
