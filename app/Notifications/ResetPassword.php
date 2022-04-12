<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', url(config('app.url') . '/password/reset/' . $this->token) . '?email=' . urlencode($notifiable->email))
            ->line('If you did not request a password reset, no further action is required.');
    }
}
