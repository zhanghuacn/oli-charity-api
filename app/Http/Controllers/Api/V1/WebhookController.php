<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Bazaar;
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
                        $this->handleBazaar($order);
                        break;
                    case Order::TYPE_ACTIVITY:
                        $this->handleActivity($order);
                        break;
                    case Order::TYPE_AUCTION:
                        $this->handleAuction($order);
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
        $ticket = new Ticket();
        $ticket->charity_id = $order->charity_id;
        $ticket->activity_id = $order->activity_id;
        $ticket->user_id = $order->user_id;
        $ticket->type = Ticket::TYPE_DONOR;
        $ticket->price = floatval($order->amount);
        if (!$order->activity->is_verification) {
            do {
                $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_BOTH);
                if (Ticket::where(['activity_id' => $ticket->activity_id, 'lottery_code' => $code])->doesntExist()) {
                    $ticket->lottery_code = $code;
                    $ticket->verified_at = Carbon::now();
                    break;
                }
            } while (true);
        }
        $ticket->save();
        $activity_total_amount = !empty($order->activity->extends['total_amount']) ? $order->activity->extends['total_amount'] : 0;
        $charity_total_amount = !empty($order->charity->extends['total_amount']) ? $order->charity->extends['total_amount'] : 0;
        $participates =!empty($order->charity->extends['participates']) ? $order->activity->extends['participates'] : 0;
        $order->activity()->update([
            'extends->participates' => bcadd(intval($participates) ?? 0, 1),
            'extends->total_amount' => bcadd(floatval($activity_total_amount), $order->amount)
        ]);
        $order->charity()->update([
            'extends->total_amount' => bcadd(floatval($charity_total_amount), $order->amount)
        ]);
        $order->activity()->decrement('stocks');
    }

    private static function handleBazaar(Order $order): void
    {
        $activity_total_amount = !empty($order->activity->extends['total_amount']) ? $order->activity->extends['total_amount'] : 0;
        $charity_total_amount = !empty($order->charity->extends['total_amount']) ? $order->charity->extends['total_amount'] : 0;
        $order->activity()->update([
            'extends->total_amount' => bcadd(floatval($activity_total_amount), $order->amount)
        ]);
        $order->charity()->update([
            'extends->total_amount' => bcadd(floatval($charity_total_amount), $order->amount)
        ]);
        $order->orderable()->decrement('stock');
        Bazaar::create([
            'charity_id' => $order->charity_id,
            'activity_id' => $order->activity_id,
            'order_id' => $order->id,
            'goods_id' => $order->orderable->id,
            'user_id' => $order->user_id,
            'price' => $order->orderable->price,
        ]);
    }

    private static function handleAuction(Order $order): void
    {
        $activity_total_amount = !empty($order->activity->extends['total_amount']) ? $order->activity->extends['total_amount'] : 0;
        $charity_total_amount = !empty($order->charity->extends['total_amount']) ? $order->charity->extends['total_amount'] : 0;
        $order->activity()->update([
            'extends->total_amount' => bcadd(floatval($activity_total_amount), $order->amount)
        ]);
        $order->charity()->update([
            'extends->total_amount' => bcadd(floatval($charity_total_amount), $order->amount)
        ]);
    }

    private static function handleActivity(Order $order): void
    {
        $activity_total_amount = !empty($order->activity->extends['total_amount']) ? $order->activity->extends['total_amount'] : 0;
        $charity_total_amount = !empty($order->charity->extends['total_amount']) ? $order->charity->extends['total_amount'] : 0;
        $order->activity()->update([
            'extends->total_amount' => bcadd(floatval($activity_total_amount), $order->amount)
        ]);
        $order->charity()->update([
            'extends->total_amount' => bcadd(floatval($charity_total_amount), $order->amount)
        ]);
        Ticket::where(['activity_id' => $order->activity_id, 'user_id' => $order->user_id])->increment('amount', $order->amount);
    }

    private static function handleCharity(Order $order): void
    {
        $charity_total_amount = !empty($order->charity->extends['total_amount']) ? $order->charity->extends['total_amount'] : 0;
        $order->charity()->update([
            'extends->total_amount' => bcadd(floatval($charity_total_amount), $order->amount)
        ]);
    }
}
