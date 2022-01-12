<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Activity;
use App\Models\Charity;
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

    private function handleTickets(Order $order): void
    {
        $activity = Activity::find($order->activity_id);
        $tickets = new Ticket([
            'charity_id' => $order->charity_id,
            'activity_id' => $activity->id,
            'user_id' => $order->user_id,
            'type' => Ticket::TYPE_DONOR,
            'price' => $order->amount,
        ]);
        $activity->increment('extends->participates');
        $activity->increment('extends->total_amount', $order->amount);
        $tickets->save();
    }

    private function handleCommon(Order $order): void
    {
        $activity = Activity::find($order->activity_id);
        $activity->increment('extends->total_amount', $order->amount);
        $activity->charity->increment('extends->total_amount', $order->amount);
    }

    private function handleCharity(Order $order): void
    {
        $charity = Charity::find($order->charity_id);
        $charity->increment('extends->total_amount', $order->amount);
    }
}
