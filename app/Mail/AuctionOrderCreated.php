<?php

namespace App\Mail;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuctionOrderCreated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private Auction $auction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Auction $auction)
    {
        $this->auction = $auction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $bid_num = $this->auction->bidRecord()->where(['user_id' => $this->auction->current_bid_user_id])->count();
        $user_count = $this->auction->bidRecord()->distinct('user_id')->count();
        return $this->subject(sprintf('You won! Pay now to receive %s.', $this->auction->name))
            ->markdown('emails.auction', [
                'auction' => $this->auction,
                'bid_num' => $bid_num,
                'name' => optional($this->auction->user)->name,
                'image' => collect($this->auction->images)->first(),
                'user_num' => $user_count > 1 ? $user_count - 1 : 0,
            ]);
    }
}
