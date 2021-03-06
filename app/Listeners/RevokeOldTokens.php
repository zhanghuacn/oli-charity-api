<?php

namespace App\Listeners;

use Illuminate\Support\Carbon;
use Laravel\Passport\Token;

class RevokeOldTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        Token::query()
            ->where('id', '<>', $event->tokenId)
            ->where('user_id', $event->userId)
            ->where('client_id', $event->clientId)
            ->where('revoked', 0)
            ->where('expires_at', '<=', Carbon::now()->toDateTimeString())
            ->delete();
    }
}
