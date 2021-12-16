<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{
    public function handleChargeSucceeded(array $payload): JsonResponse|JsonResource
    {
        $data = $payload['data']['object'];
        Log::info('stripe_charge_succeeded:', $data);
        Order::where(['payment_no' => $data['id']])->update([
            'payment_status' => Order::STATUS_IN_PAYMENT,
        ]);
        return Response::ok();
    }

    public function handlePaymentIntentSucceeded(array $payload): JsonResponse|JsonResource
    {
        $data = $payload['data']['object'];
        Log::info('stripe_payment_intent_succeeded:', $data);
        $order = Order::where(['payment_no' => 'pi_3K6zaXHP0UsCblE91vSdFqJs'])->firstOrFail();
        $order->payment_status = Order::STATUS_PAID;
        $order->payment_time = Carbon::now();
        $order->save();
        return Response::ok();
    }

    public function handlePaymentIntentPaymentFailed(array $payload): JsonResponse
    {
        $data = $payload['data']['object'];
        Log::info('stripe_payment_intent_failed:', $data);
        $order = Order::where(['payment_no' => 'pi_3K6zaXHP0UsCblE91vSdFqJs'])->firstOrFail();
        $order->payment_status = Order::STATUS_FAIL;
        $order->save();
        return Response::ok();
    }
}
