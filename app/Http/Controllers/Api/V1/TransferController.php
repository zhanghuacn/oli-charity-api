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
        $ticket = $activity->ticket();
        $request->merge([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
        ]);
        $data = Transfer::filter($request->all())->simplePaginate($request->input('per_page', 10));
        return Response::success(new TransferCollection($data));
    }

    public function transfer(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'voucher' => 'required|array',
            'voucher.*' => 'required|url',
        ]);
        $ticket = $activity->ticket();
        $order = $this->orderService->transfer($activity, $ticket, 0, $request->get('voucher'));
        return Response::success([
            'order_sn' => $order->order_sn,
        ]);
    }

    public function check(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'transfer_sn' => 'required|exists:transfers,transfer_sn,activity_id,' . $activity->id,
            'amount' => 'required|confirmed|numeric|min:1|not_in:0',
            'status' => 'required|in:PASSED,REFUSE',
            'remark' => 'sometimes|string',
        ]);
        try {
            DB::transaction(function () use ($request) {
                $transfer = Transfer::whereTransferSn($request->get('transfer_sn'))->first();
                $transfer->amount = $request->get('amount');
                $transfer->status = $request->get('status');
                $transfer->remark = $request->get('remakr');
                $transfer->verified_at = Carbon::now();
                $transfer->save();

                $order = Order::where('extends->transfer_sn', $transfer->transfer_sn)->firstOrFail();
                $order->payment_status = $transfer->status == Transfer::STATUS_PASSED ? Order::STATUS_PAID : Order::STATUS_FAIL;
                if ($transfer->status == Transfer::STATUS_PASSED) {
                    $order->payment_time = Carbon::now();
                }
                $order->save();
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }
}
