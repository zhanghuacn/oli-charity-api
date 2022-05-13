<?php

namespace App\Console\Commands;

use App\Mail\AuctionOrderCreated;
use App\Mail\CaptchaShipped;
use App\Models\Auction;
use App\Models\Order;
use App\Models\User;
use Aws\Sns\SnsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuctionOrders extends Command
{
    protected $signature = 'auction:order';

    protected $description = '生成竞拍订单';

    /**
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        Auction::where([['is_auction', '=', true], ['end_time', '<', now()]])->get()
            ->each(function (Auction $auction) {
                DB::transaction(function () use ($auction) {
                    if (!empty($auction->current_bid_user_id)) {
                        $order = new Order();
                        $order->user_id = $auction->current_bid_user_id;
                        $order->type = Order::TYPE_AUCTION;
                        $order->charity_id = $auction->charity_id;
                        $order->activity_id = $auction->activity_id;
                        $order->currency = Str::lower(Config::get('cashier.currency'));
                        $order->amount = $auction->current_bid_price;
                        $order->fee_amount = 0;
                        $order->total_amount = $auction->current_bid_price;
                        $order->orderable()->associate($auction);
                        $order->save();
                        $user = User::findOrFail($auction->current_bid_user_id);
                        if (!empty($user)) {
                            Mail::to($user->email)->send(new AuctionOrderCreated($auction));
                        }
                    }
                    $auction->is_auction = false;
                    $auction->save();
                });
            });
    }
}
