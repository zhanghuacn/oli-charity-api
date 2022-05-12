<?php

namespace App\Broadcasting;

use App\Models\Auction;
use App\Models\User;

class AuctionChannel
{
    public function __construct()
    {
    }

    public function join(User $user, Auction $auction): bool
    {
//        return $auction->activity->tickets()->where(['user_id' => $user->id])->exists();
        return true;
    }
}
