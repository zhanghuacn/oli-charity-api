<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Apply;
use App\Models\Ticket;
use App\Models\Transfer;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        abort_if(Carbon::parse($activity->end_time)->tz(config('app.timezone'))->lt(Carbon::now()->tz(config('app.timezone'))), 422, 'Event ended');
        abort_if(!empty($activity->my_ticket), 422, 'Tickets purchased');
        abort_if(empty($activity->charity->stripe_account_id), 422, 'Unbound payment platform account');
        abort_if($activity->stocks <= 0, 422, 'Tickets have been sold out');
        $order = $this->orderService->tickets(Auth::user(), $activity);
        return Response::success([
            'stripe_account_id' => $activity->charity->stripe_account_id,
            'order_sn' => $order->order_sn,
            'client_secret' => $order->extends['client_secret']
        ]);
    }

    public function collection(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-apply', $activity);
        abort_if($activity->price != 0, 403, 'Permission denied');
        abort_if(Carbon::parse($activity->end_time)->tz(config('app.timezone'))->lt(Carbon::now()->tz(config('app.timezone'))), 422, 'Event ended');
        abort_if(!empty($activity->my_ticket), 422, 'Tickets Repeat Claim');
        abort_if($activity->stocks <= 0, 422, 'Tickets have been sold out');
        DB::transaction(function () use ($activity) {
            $ticket = new Ticket([
                'charity_id' => $activity->charity_id,
                'activity_id' => $activity->id,
                'user_id' => Auth::id(),
                'type' => Ticket::TYPE_DONOR,
                'price' => $activity->price,
            ]);
            $ticket->save();
            $activity->update(['extends->participates' => bcadd(intval($activity->extends['participates']) ?? 0, 1)]);
            $activity->decrement('stocks');
            if (!$activity->is_verification) {
                do {
                    $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_BOTH);
                    if (Ticket::where(['activity_id' => $activity->id, 'lottery_code' => $code])->doesntExist()) {
                        $ticket->lottery_code = $code;
                        $ticket->verified_at = Carbon::now()->tz(config('app.timezone'));
                        break;
                    }
                } while (true);
            }
        });
        return Response::success();
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
                $ticket->update(['lottery_code' => $code, 'verified_at' => Carbon::now()->tz(config('app.timezone'))]);
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
        $ticket = $activity->my_ticket;
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
        $data = $activity->tickets()->where(['type' => Ticket::TYPE_DONOR])->with(['user', 'transfers'])->filter($request->all())->get()
            ->transform(function ($item) {
                return [
                    'id' => $item->user->id,
                    'type' => $item->type,
                    'name' => $item->user->name,
                    'avatar' => $item->user->avatar,
                    'profile' => $item->user->profile,
                    'lottery_code' => $item->lottery_code,
                    'seat_num' => $item->seat_num,
                    'transfer' => $item->transfers->transform(function (Transfer $transfer) {
                        return [
                            'id' => $transfer->id,
                            'created_at' => $transfer->created_at,
                            'amount' => floatval($transfer->amount),
                            'voucher' => $transfer->voucher,
                            'status' => $transfer->status,
                        ];
                    })
                ];
            });
        return Response::success($data ?? []);
    }

    public function state(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'code' => 'required|exists:tickets,code',
        ]);
        return Response::success(['verified_at' => optional($activity->my_ticket)->verified_at ?
            Carbon::parse($activity->my_ticket->verified_at)->tz(config('app.timezone'))->format('Y-m-d H:i:s')
            : null]);
    }
}
