<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use function abort;

class WebhookController extends CashierController
{
    public function handleChargeSucceeded(array $payload): JsonResponse|JsonResource
    {
        try {
            DB::transaction(function () use ($payload) {
                $data = $payload['data']['object'];
                Log::info('stripe_charge_succeeded:', $data);
                Order::where(['payment_no' => $data['id']])->update([
                    'payment_status' => Order::STATUS_IN_PAYMENT,
                ]);
            });
        } catch (\Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::ok();
    }

    public function handlePaymentIntentSucceeded(array $payload): JsonResponse|JsonResource
    {
        try {
            DB::transaction(function () use ($payload) {
                $data = $payload['data']['object'];
                Log::info('stripe_payment_intent_succeeded:', $data);
                $order = Order::where(['payment_no' => $data['id']])->firstOrFail();
                $order->payment_status = Order::STATUS_PAID;
                $order->payment_time = Carbon::now();
                $order->save();

                switch ($order->type) {
                    case Order::TYPE_TICKETS:
                        $this->handleTickets($order);
                        break;
                    case Order::TYPE_CHARITY:
                        $this->handleCharity($order);
                        break;
                    case Order::TYPE_BAZAAR:
                    case Order::TYPE_ACTIVITY:
                        $this->handleCommon($order);
                        break;
                    default:
                }
            });
        } catch (\Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::ok();
    }

    public function handlePaymentIntentPaymentFailed(array $payload): JsonResponse
    {
        try {
            DB::transaction(function () use ($payload) {
                $data = $payload['data']['object'];
                Log::info('stripe_payment_intent_failed:', $data);
                $order = Order::where(['payment_no' => $data['id']])->firstOrFail();
                $order->payment_status = Order::STATUS_FAIL;
                $order->save();
            });
        } catch (\Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::ok();
    }

    public function handleTickets(Order $order): void
    {
        $tickets = new Ticket([
            'charity_id' => $order->charity_id,
            'activity_id' => $order->activity_id,
            'user_id' => $order->user_id,
            'type' => Ticket::TYPE_DONOR,
            'price' => floatval($order->amount),
        ]);
        $tickets->save();
        $order->activity()->update([
            'extends->participates' => bcadd(intval($order->activity->extends['participates']) ?? 0, 1),
            'extends->total_amount' => bcadd(floatval($order->activity->extends['total_amount']) ?? 0, $order->amount)
        ]);
        $order->activity()->decrement('stocks');
        $order->charity()->update([
            'extends->total_amount' => bcadd(floatval($order->charity->extends['total_amount']) ?? 0, $order->amount)
        ]);
    }

    private static function handleCommon(Order $order): void
    {
        $order->activity()->update([
            'extends->total_amount' => bcadd(floatval($order->activity->extends['total_amount']) ?? 0, $order->amount)
        ]);
        $order->charity()->update([
            'extends->total_amount' => bcadd(floatval($order->charity->extends['total_amount']) ?? 0, $order->amount)
        ]);
        if ($order->type == Order::TYPE_ACTIVITY) {
            $order->activity->tickets()->where(['user_id' => $order->user_id])->increment('amount', $order->amount);
        }
    }

    private static function handleCharity(Order $order): void
    {
        $order->charity()->update([
            'extends->total_amount' => bcadd(floatval($order->charity->extends['total_amount']) ?? 0, $order->amount)
        ]);
    }
}
