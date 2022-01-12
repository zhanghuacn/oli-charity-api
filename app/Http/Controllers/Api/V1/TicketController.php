<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Apply;
use App\Models\Ticket;
use App\Models\Transfer;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function abort_if;

class TicketController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function buyTicket(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-apply', $activity);
        abort_if(!empty($activity->my_ticket), 422, 'Tickets purchased');
        $order = $this->orderService->tickets(Auth::user(), $activity);
        return Response::success([
            'stripe_account_id' => $activity->charity->stripe_account_id,
            'order_sn' => $order->order_sn,
            'client_secret' => $order->extends['client_secret']
        ]);
    }

    public function anonymous(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $request->validate([
            'enable' => 'required|boolean',
        ]);
        $activity->tickets()->where(['user_id' => Auth::id()])->firstOrFail()->update(['anonymous' => $request['enable']]);
        return Response::success();
    }

    public function scan(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-staff', $activity);
        $request->validate([
            'code' => 'required|exists:tickets,code',
        ]);
        $ticket = Ticket::where(['code' => $request['code']])->firstOrFail();
        abort_if($ticket->verified_at != null, 422, 'The QR code has been signed in. Please do not repeat the operation');
        do {
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_BOTH);
            if (Ticket::where(['activity_id' => $ticket->activity_id, 'lottery_code' => $code])->doesntExist()) {
                $ticket->update(['lottery_code' => $code, 'verified_at' => now()]);
                break;
            }
        } while (true);
        return Response::success([
            'seat_num' => $ticket->seat_num,
            'code' => $ticket->code,
        ]);
    }

    public function myTickets(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $ticket = $activity->ticket;
        return Response::success([
            'code' => $ticket->code,
            'lottery_code' => $ticket->lottery_code,
            'seat_num' => $ticket->seat_num,
        ]);
    }

    public function guests(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-staff', $activity);
        $request->validate([
            'filter' => 'sometimes|in:COMPLETED,INCOMPLETE',
            'name' => 'sometimes|string',
            'sort' => 'sometimes|in:ASC,DESC',
        ]);
        abort_if($activity->tickets()->where(['user_id' => Auth::id(), 'type' => Ticket::TYPE_STAFF])->doesntExist(), 403, 'Permission denied');
        $data = $activity->tickets()->with(['user', 'transfers'])->filter($request->all())->get()
            ->transform(function ($item) {
                return [
                    'id' => $item->user->id,
                    'type' => $item->type,
                    'name' => $item->user->name,
                    'avatar' => $item->user->avatar,
                    'profile' => $item->user->profile,
                    'lottery_code' => $item->lottery_code,
                    'transfer' => $item->transfers->transform(function (Transfer $transfer) {
                        return [
                            'id' => $transfer->id,
                            'created_at' => $transfer->created_at,
                            'amount' => $transfer->amount,
                            'voucher' => $transfer->voucher
                        ];
                    })
                ];
            });
        return Response::success($data);
    }
}
