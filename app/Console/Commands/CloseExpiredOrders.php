<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;

class CloseExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '关闭过期订单';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Order::where(['payment_status' => Order::STATUS_IN_PAYMENT])
            ->whereRaw('now() > ADDDATE(created_at,interval 15 day)')
            ->get()->each(function (Order $order) {
                $order->payment_status = Order::STATUS_CLOSED;
                $order->save();
                if ($order->type == Order::TYPE_AUCTION) {
                    $order->user()->update(['status' => User::STATUS_FROZEN, 'frozen_at' => now(), 'status_remark' => '竞拍订单未支付']);
                }
            });
    }
}
