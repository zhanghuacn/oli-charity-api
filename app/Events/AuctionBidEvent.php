<?php

namespace App\Events;

use App\Models\AuctionBidRecord;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionBidEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public AuctionBidRecord $auctionBidRecord;

    public function __construct(AuctionBidRecord $auctionBidRecord)
    {
        $this->auctionBidRecord = $auctionBidRecord;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel(sprintf('auction.%d', $this->auctionBidRecord->auction_id));
    }

    /**
     * 获取要广播的数据。
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->auctionBidRecord->id,
            'price' => $this->auctionBidRecord->price,
            'bid_price' => $this->auctionBidRecord->bid_price,
            'user' => [
                'id' => $this->auctionBidRecord->user->id,
                'name' => $this->auctionBidRecord->user->name,
                'avatar' => $this->auctionBidRecord->user->avatar,
            ],
            'created_at' => Carbon::parse($this->auctionBidRecord->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
