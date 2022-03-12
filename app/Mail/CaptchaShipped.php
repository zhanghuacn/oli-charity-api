<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaptchaShipped extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private string $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject(sprintf('%s Email Verification Code！', config('app.name')))
            ->markdown('emails.captcha', [
                'code' => $this->code,
            ]);
    }
}
