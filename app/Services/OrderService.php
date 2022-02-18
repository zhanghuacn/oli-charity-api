<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Charity;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

    public function bazaar(User $user, Activity $activity, Goods $goods): Order
    {
        try {
            return DB::transaction(function () use ($activity, $user, $goods) {
                $payment_intent = PaymentIntent::create([
                    'payment_method_types' => ['card'],
                    'amount' => $goods->price * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'application_fee_amount' => 0,
                    'receipt_email' => $user->email,
                ], ['stripe_account' => $activity->charity->stripe_account_id]);
                $order = new Order([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_BAZAAR,
                    'charity_id' => $activity->charity->id,
                    'activity_id' => $activity->id,
                    'currency' => Str::lower(Config::get('cashier.currency')),
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
                    'receipt_email' => $user->email,
                ], ['stripe_account' => $activity->charity->stripe_account_id]);
                $order = new Order([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_ACTIVITY,
                    'charity_id' => $activity->charity_id,
                    'activity_id' => $activity->id,
                    'currency' => Str::lower(Config::get('cashier.currency')),
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

    public function tickets(User $user, Activity $activity): Order
    {
        try {
            return DB::transaction(function () use ($activity, $user) {
                $payment_intent = PaymentIntent::create([
                    'payment_method_types' => ['card'],
                    'amount' => $activity->price * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'application_fee_amount' => 0,
                    'receipt_email' => $user->email,
                ], ['stripe_account' => $activity->charity->stripe_account_id]);
                $order = new Order([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_TICKETS,
                    'charity_id' => $activity->charity_id,
                    'activity_id' => $activity->id,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'amount' => $activity->price,
                    'fee_amount' => 0,
                    'total_amount' => $activity->price,
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
                    'receipt_email' => $user->email,
                ], ['stripe_account' => $charity->stripe_account_id]);
                $order = new Order([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_CHARITY,
                    'charity_id' => $charity->id,
                    'currency' => Str::lower(Config::get('cashier.currency')),
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

    public function transfer(Activity $activity, Ticket $ticket, mixed $amount, mixed $voucher): Order
    {
        try {
            return DB::transaction(function () use ($voucher, $amount, $ticket, $activity) {
                $transfer = new Transfer();
                $transfer->charity_id = $activity->charity_id;
                $transfer->activity_id = $activity->id;
                $transfer->ticket_id = $ticket->id;
                $transfer->user_id = $ticket->user_id;
                $transfer->amount = $amount;
                $transfer->voucher = $voucher;
                $transfer->status = Transfer::STATUS_WAIT;
                $transfer->save();

                $order = new Order([
                    'user_id' => Auth::id(),
                    'type' => Order::TYPE_ACTIVITY,
                    'charity_id' => $activity->charity_id,
                    'activity_id' => $activity->id,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'amount' => $amount,
                    'fee_amount' => 0,
                    'total_amount' => $amount,
                    'payment_type' => Order::PAYMENT_OFFLINE,
                    'payment_no' => $transfer->code,
                ]);
                $order->orderable()->associate($activity);
                $order->save();
                return $order;
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }
}
