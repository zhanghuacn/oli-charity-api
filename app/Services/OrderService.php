<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Charity;
use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Throwable;

class OrderService
{
    public function __construct()
    {
        Stripe::setApiKey(Config::get('cashier.secret'));
    }

    public function bazaar(User $user, Charity $charity, Goods $goods): Order
    {
        try {
            return DB::transaction(function () use ($charity, $user, $goods) {
                $payment_intent = PaymentIntent::create([
                    'payment_method_types' => ['card'],
                    'amount' => $goods->price * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'application_fee_amount' => 0,
                ], ['stripe_account' => $charity->stripe_account]);
                $order = new Order([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_BAZAAR,
                    'charity_id' => $charity->id,
                    'currency' => 'aud',
                    'amount' => $goods->price,
                    'fee_amount' => 0,
                    'total_amount' => $goods->price,
                    'payment_no' => $payment_intent->id,
                    'extends' => [
                        'client_secret' => $payment_intent->client_secret,
                    ]
                ]);
                $order->orderable()->associate($goods);
                $order->save();
                return $order;
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }

    public function activity(User $user, Activity $activity, $amount): Order
    {
        try {
            return DB::transaction(function () use ($activity, $user, $amount) {
                $payment_intent = PaymentIntent::create([
                    'payment_method_types' => ['card'],
                    'amount' => $amount * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'application_fee_amount' => 0,
                ], ['stripe_account' => $activity->charity->stripe_account]);
                $order = new Order([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_ACTIVITY,
                    'charity_id' => $activity->charity->id,
                    'currency' => 'aud',
                    'amount' => $amount,
                    'fee_amount' => 0,
                    'total_amount' => $amount,
                    'payment_no' => $payment_intent->id,
                    'extends' => [
                        'client_secret' => $payment_intent->client_secret,
                    ]
                ]);
                $order->orderable()->associate($activity);
                $order->save();
                return $order;
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }

    public function charity(User $user, Charity $charity, $amount): Order
    {
        try {
            return DB::transaction(function () use ($charity, $user, $amount) {
                $payment_intent = PaymentIntent::create([
                    'payment_method_types' => ['card'],
                    'amount' => $amount * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'application_fee_amount' => 0,
                ], ['stripe_account' => $charity->stripe_account]);
                $order = new Order([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_CHARITY,
                    'charity_id' => $charity->id,
                    'currency' => 'aud',
                    'amount' => $amount,
                    'fee_amount' => 0,
                    'total_amount' => $amount,
                    'payment_no' => $payment_intent->id,
                    'extends' => [
                        'client_secret' => $payment_intent->client_secret,
                    ]
                ]);
                $order->orderable()->associate($charity);
                $order->save();
                return $order;
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }
}
