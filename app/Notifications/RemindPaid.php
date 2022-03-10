<?php

namespace App\Notifications;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return (new MailMessage())->subject('Imagine 2080 Reminder！')->view(
            'emails.SendEventReminder',
            ['event' => $this->activity->name, 'days' => $this->days]
        );
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Imagine 2080 Reminder！',
            'content' => 'A friendly reminder you ' . $this->activity->name . ' Cub will be held in next ' . $this->days . ' days. Please confirm you have registered and Raffle ticket number.',
            'activity_id' => $this->activity->id,
        ];
    }

//    public function toSns($notifiable): SnsMessage
//    {
//        $event = $this->activity->name;
//        $message = <<<EOF
//A friendly reminder you $event will be held in next $this->days days.
//Please confirm you have registered and Raffle ticket number. .
//EOF;
//
//        return SnsMessage::create([
//            'body' => $message,
//            'promotional' => true,
//            'sender' => 'Imagine2080',
//        ]);
//    }
}
