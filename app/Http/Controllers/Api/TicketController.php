<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserCollection;
use App\Models\Activity;
use App\Models\ActivityApplyRecord;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class TicketController extends Controller
{
    public function apply(Activity $activity): JsonResponse|JsonResource
    {
        abort_if(!$activity->is_private, 403, 'Permission denied');
        $apply = $activity->applies()->firstOrCreate([
            'charity_id' => $activity->charity_id,
            'user_id' => Auth::id(),
        ]);
        abort_if($apply->status == ActivityApplyRecord::STATUS_REFUSE, 403, 'Permission denied');
        return Response::success();
    }

    public function buyTicket(Activity $activity): JsonResponse|JsonResource
    {
        abort_if(
            $activity->is_private &&
            $activity->applies()->where(['user_id' => Auth::id(), 'status' => ActivityApplyRecord::STATUS_PASSED])->doesntExist(),
            403,
            'Permission denied'
        );
        return Response::success();
    }

    public function anonymous(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'enable' => 'required|boolean',
        ]);
        $activity->tickets()->where(['user_id' => Auth::id()])->firstOrFail()->update(['anonymous' => $request['enable']]);
        return Response::success();
    }

    public function scan(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        abort_if($activity->tickets()->where(['user_id' => Auth::id(), 'type' => Ticket::TYPE_STAFF])->doesntExist(), 403, 'Permission denied');
        $request->validate([
            'code' => 'required|exists:tickets,code',
        ]);
        $ticket = Ticket::where(['code' => $request['code']])->firstOrFail();
        abort_if($ticket->verified_at != null, 400, 'Do not repeat the verification');
        $ticket->verified_at = Carbon::now();
        do {
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_BOTH);
            if (Ticket::where(['activity_id' => $ticket->activity_id, 'lottery_code' => $code])->doesntExist()) {
                $ticket->lottery_code = $code;
                break;
            }
        } while (true);
        $ticket->save();
        return Response::success([
            'table_num' => $ticket->table_num,
            'code' => $ticket->code,
        ]);
    }

    public function myTickets(Activity $activity): JsonResponse|JsonResource
    {
        $ticket = $activity->tickets()->where(['user_id' => Auth::id()])->select('code, table_num, lottery_code')->firstOrFail();
        return Response::success($ticket);
    }

    public function guests(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'filter' => 'sometimes|in:COMPLETED,INCOMPLETE',
            'name' => 'sometimes|string',
            'sort' => 'sometimes|in:ASC,DESC',
        ]);
        abort_if($activity->tickets()->where(['user_id' => Auth::id(), 'type' => Ticket::TYPE_STAFF])->doesntExist(), 403, 'Permission denied');
        $data = $activity->tickets()->with('user')->filter($request->all())->get()
            ->transform(function ($item) {
                return [
                    'id' => $item->user->id,
                    'type' => $item->type,
                    'name' => $item->user->name,
                    'avatar' => $item->user->avatar,
                    'profile' => $item->user->profile,
                    'lottery_code' => $item->lottery_code,
                    'is_sign' => !is_null($item->verified_at)
                ];
            });
        return Response::success($data);
    }
}
