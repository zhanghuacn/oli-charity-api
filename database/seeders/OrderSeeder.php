<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Order::truncate();
        $order = new Order([
            'user_id' => 1,
            'type' => Order::TYPE_TICKETS,
            'charity_id' => 1,
            'currency' => Str::lower(Config::get('cashier.currency')),
            'amount' => 100,
            'fee_amount' => 0,
            'total_amount' => 100,
            'payment_no' => '123123123123123',
            'payment_type' => Order::PAYMENT_ONLINE,
            'payment_status' => Order::STATUS_PAID,
            'payment_time' => Carbon::now()->tz(config('app.timezone')),
        ]);
        $order->orderable()->associate(Activity::find(1));
        $order->save();
    }
}
