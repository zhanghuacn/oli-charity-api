<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{

    public function handleChargeSucceeded(array $payload): JsonResponse|JsonResource
    {
        $data = $payload['data']['object'];
        Order::where(['payment_no' => $data['id']])->update([
            'payment_status' => Order::STATUS_IN_PAYMENT,
        ]);
        return Response::ok();
    }

    public function handlePaymentIntentSucceeded(array $payload): JsonResponse|JsonResource
    {
        $data = $payload['data']['object'];
        Order::where(['payment_no' => $data['id']])->update([
            'payment_status' => Order::STATUS_PAID,
            'payment_time' => $data['created'],
        ]);
        return Response::ok();
    }

    public function handlePaymentIntentPaymentFailed(array $payload): JsonResponse
    {
        $data = $payload['data']['object'];
        Order::where(['payment_no' => $data['id']])->update([
            'payment_status' => Order::STATUS_FAIL,
        ]);
        return Response::ok();
    }
}
