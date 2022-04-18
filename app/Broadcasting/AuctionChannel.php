<?php

namespace App\Broadcasting;

use App\Models\Auction;
use App\Models\Ticket;
use App\Models\User;

class AuctionChannel
{
    public function __construct()
    {

    }

    public function join(User $user, Auction $auction): bool
    {
//        return Ticket::where(['user_id' => $user->id, 'activity_id' => $auction->activity_id])->exists();
        return true;
    }
}
