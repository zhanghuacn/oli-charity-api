<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TransferCollection;
use App\Models\Activity;
use App\Models\Order;
use App\Models\Transfer;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Throwable;
use function abort;

class TransferController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $ticket = $activity->my_ticket;
        $request->merge([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
        ]);
        $data = Transfer::filter($request->all())->paginate($request->input('per_page', 10));
        return Response::success(new TransferCollection($data));
    }

    public function transfer(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'voucher' => 'required|array',
            'voucher.*' => 'required|url',
        ]);
        $ticket = $activity->my_ticket;
        $order = $this->orderService->transfer($activity, $ticket, 0, $request->get('voucher'));
        return Response::success([
            'order_sn' => $order->order_sn,
        ]);
    }

    public function check(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'transfer_id' => 'required|exists:transfers,id,activity_id,' . $activity->id,
            'status' => 'required|in:PASSED,REFUSE',
            'amount' => 'required_if:status,PASSED|confirmed|numeric|min:1|not_in:0',
            'remark' => 'sometimes|string',
        ]);
        $transfer = Transfer::findOrFail($request->get('transfer_id'));
        abort_if($transfer->status != Transfer::STATUS_WAIT, '422', 'Please do not review again');
        try {
            DB::transaction(function () use ($transfer, $request) {
                $transfer->amount = $request->get('amount') ?? 0;
                $transfer->status = $request->get('status');
                $transfer->remark = $request->get('remakr');
                $transfer->verified_at = Carbon::tz(config('app.timezone'))->Carbon::tz(config('app.timezone'))->now();
                $transfer->save();

                $order = Order::wherePaymentNo($transfer->code)->firstOrFail();
                $order->payment_status = $transfer->status == Transfer::STATUS_PASSED ? Order::STATUS_PAID : Order::STATUS_CLOSED;
                $order->amount = $transfer->amount;
                $order->fee_amount = 0;
                $order->total_amount = $transfer->amount;
                $order->payment_time = Carbon::tz(config('app.timezone'))->now();
                $order->save();

                $transfer->ticket()->increment('amount', $transfer->amount);
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }
}
