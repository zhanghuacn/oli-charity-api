<?php

namespace App\Listeners;

use Laravel\Passport\RefreshToken;

class PruneOldTokens
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
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        RefreshToken::query()
            ->where('id', '<>', $event->refreshTokenId)
            ->where('access_token_id', '<>', $event->accessTokenId)
            ->delete();
    }
}
