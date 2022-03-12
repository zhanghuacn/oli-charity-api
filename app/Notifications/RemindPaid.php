<?php

namespace App\Notifications;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class RemindPaid extends Notification implements ShouldQueue
{
    use Queueable;

    private Activity $activity;
    private int $days;

    public function __construct(Activity $activity, int $days)
    {
        $this->activity = $activity;
        $this->days = $days;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = sprintf('%s/events/detail/%d', Str::of(config('app.url'))->replace('api', 'www')->value(), $this->activity->id);
        return (new MailMessage())
            ->subject(sprintf('%s Event Reminder！', config('app.name')))
            ->markdown('emails.remind', [
                'event' => $this->activity->name, 'days' => $this->days, 'url' => $url,
            ]);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Imagine 2080 Reminder！',
            'content' => 'A friendly reminder you ' . $this->activity->name . ' Cub will be held in next ' . $this->days . ' days. Please confirm you have registered and Raffle ticket number.',
            'activity_id' => $this->activity->id,
        ];
    }
}
