<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

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
