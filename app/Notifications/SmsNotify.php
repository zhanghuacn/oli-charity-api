<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\AwsSns\SnsChannel;
use NotificationChannels\AwsSns\SnsMessage;

class SmsNotify extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $content;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return [SnsChannel::class];
    }

    /**
     * @param $notifiable
     * @return SnsMessage
     */
    public function toSns($notifiable): SnsMessage
    {
        return SnsMessage::create()
            ->body($this->content)
            ->promotional()
            ->sender(config('app.name'));
    }
}
