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
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Throwable;

class OrderService
{
    public function __construct()
    {
        Stripe::setApiKey(Config::get('cashier.secret'));
    }

    public function bazaar(User $user, Activity $activity, Goods $goods, string $paymentMethod = null): Order
    {
        try {
            return DB::transaction(function () use ($paymentMethod, $activity, $user, $goods) {
                $data = [
                    'amount' => $goods->price * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'payment_method_types' => ['card'],
                ];
                if (!empty($paymentMethod)) {
                    $payment_method = PaymentMethod::create([
                        'customer' => $user->stripeId(),
                        'payment_method' => $paymentMethod,
                    ], [
                        'stripe_account' => $activity->charity->stripe_account_id,
                    ]);
                    $data['payment_method'] = $payment_method->id;
                }
                $payment_intent = PaymentIntent::create($data, ['stripe_account' => $activity->charity->stripe_account_id]);
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

    public function activity(User $user, Activity $activity, $amount, string $paymentMethod = null): Order
    {
        try {
            return DB::transaction(function () use ($paymentMethod, $activity, $user, $amount) {
                $data = [
                    'amount' => $amount * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'payment_method_types' => ['card'],
                ];
                if (!empty($paymentMethod)) {
                    $payment_method = PaymentMethod::create([
                        'customer' => $user->stripeId(),
                        'payment_method' => $paymentMethod,
                    ], [
                        'stripe_account' => $activity->charity->stripe_account_id,
                    ]);
                    $data['payment_method'] = $payment_method->id;
                }
                $payment_intent = PaymentIntent::create($data, ['stripe_account' => $activity->charity->stripe_account_id]);
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

    public function tickets(User $user, Activity $activity, string $paymentMethod = null): Order
    {
        try {
            return DB::transaction(function () use ($paymentMethod, $activity, $user) {
                $data = [
                    'amount' => $activity->price * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'payment_method_types' => ['card'],
                ];
                if (!empty($paymentMethod)) {
                    $payment_method = PaymentMethod::create([
                        'customer' => $user->stripeId(),
                        'payment_method' => $paymentMethod,
                    ], [
                        'stripe_account' => $activity->charity->stripe_account_id,
                    ]);
                    $data['payment_method'] = $payment_method->id;
                }
                $payment_intent = PaymentIntent::create($data, ['stripe_account' => $activity->charity->stripe_account_id]);
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

    public function charity(User $user, Charity $charity, float $amount, string $paymentMethod = null): Order
    {
        try {
            return DB::transaction(function () use ($paymentMethod, $charity, $user, $amount) {
                $data = [
                    'amount' => $amount * 100,
                    'currency' => Str::lower(Config::get('cashier.currency')),
                    'payment_method_types' => ['card'],
                ];
                if (!empty($paymentMethod)) {
                    $payment_method = PaymentMethod::create([
                        'customer' => $user->stripeId(),
                        'payment_method' => $paymentMethod,
                    ], [
                        'stripe_account' => $charity->stripe_account_id,
                    ]);
                    $data['payment_method'] = $payment_method->id;
                }
                $payment_intent = PaymentIntent::create($data, ['stripe_account' => $charity->stripe_account_id]);
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
                        'payment_method' => $payment_method->id ?? null,
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
