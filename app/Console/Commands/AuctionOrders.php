<?php

namespace App\Console\Commands;

use App\Mail\AuctionOrderCreated;
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

    private SnsClient $snsClient;

    public function __construct(SnsClient $snsClient)
    {
        parent::__construct();
        $this->snsClient = $snsClient;
    }


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
                    }
                    $auction->is_auction = false;
                    $auction->save();
                });
                $user = User::findOrFail($auction->current_bid_user_id);
                if (!empty($user->email)) {
                    Mail::to($user->email)->send(new AuctionOrderCreated($auction));
                }
                if (!empty($user->phone)) {
                    $bid_num = $auction->bidRecord()->where(['user_id' => $auction->current_bid_user_id])->count();
                    $user_count = $auction->bidRecord()->distinct('user_id')->count();

                    $this->snsClient->publish([
                        'Message' => sprintf(
                            "【%s】Congratulations! You've won the auction with an AU $%s. Next, please make a payment to receive your item. You placed %s bids and beat %s bidders.",
                            config('app.name'),
                            $auction->current_bid_price,
                            $bid_num,
                            $user_count
                        ),
                        'PhoneNumber' => sprintf('+%s', $user->phone),
                        'MessageAttributes' => [
                            'AWS.SNS.SMS.SMSType' => [
                                'DataType' => 'String',
                                'StringValue' => 'Transactional',
                            ]
                        ],
                    ]);
                }
            });
    }
}
