<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Charity\ApplyCollection;
use App\Models\Activity;
use App\Models\Apply;
use App\Models\Ticket;
use App\Notifications\ApplyPaid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ApplyController extends Controller
{
    public function index(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'status' => 'sometimes|in:WAIT,PASSED,REFUSE',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $applies = $activity->applies()->filter($request->all())->with('user')->paginate($request->input('per_page', 15));
        return Response::success(new ApplyCollection($applies));
    }

    public function audit(Request $request, Activity $activity, Apply $apply): JsonResponse|JsonResource
    {
        abort_if($activity->charity_id != getPermissionsTeamId() || $apply->activity_id != $activity->id, 403, 'Permission denied');
        abort_if($apply->status != Apply::STATUS_WAIT, 422, 'Abnormal application status');
        $request->validate([
            'status' => 'required|in:PASSED,REFUSE',
            'remark' => 'sometimes|string',
        ]);
        if ($request->get('status') == 'PASSED' && $activity->price == 0 && Carbon::parse($activity->end_time)->gte(Carbon::now()) && $activity->stocks > 0) {
            DB::transaction(function () use ($apply, $activity) {
                $ticket = new Ticket([
                    'charity_id' => $activity->charity_id,
                    'activity_id' => $activity->id,
                    'user_id' => $apply->user_id,
                    'type' => Ticket::TYPE_DONOR,
                    'price' => $activity->price,
                ]);
                if (!$activity->is_verification) {
                    do {
                        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_BOTH);
                        if (Ticket::where(['activity_id' => $activity->id, 'lottery_code' => $code])->doesntExist()) {
                            $ticket->lottery_code = $code;
                            $ticket->verified_at = Carbon::now();
                            break;
                        }
                    } while (true);
                }
                $ticket->save();
                $activity->update([
                    'extends->participates' => bcadd(intval($activity->extends['participates']) ?? 0, 1)
                ]);
                $activity->decrement('stocks');
            });
        }
        $apply->status = $request->get('status');
        $apply->remark = $request->get('remark');
        $apply->reviewer = Auth::id();
        $apply->reviewed_at = Carbon::now();
        $apply->save();
        $apply->user->notify(new ApplyPaid($activity));
        return Response::success();
    }


    public function batchAudit(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        abort_if($activity->charity_id != getPermissionsTeamId(), 403, 'Permission denied');
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:applies,id,activity_id,' . $activity->id,
            'status' => 'required|in:PASSED,REFUSE',
            'remark' => 'sometimes|string',
        ]);
        Apply::whereIn('id', $request->get('ids'))->get()->map(function (Apply $apply) use ($activity, $request) {
            if ($request->get('status') == 'PASSED' && $activity->price == 0 && Carbon::parse($activity->end_time)->gte(Carbon::now()) && $activity->stocks > 0) {
                DB::transaction(function () use ($apply, $activity) {
                    $ticket = new Ticket([
                        'charity_id' => $activity->charity_id,
                        'activity_id' => $activity->id,
                        'user_id' => $apply->user_id,
                        'type' => Ticket::TYPE_DONOR,
                        'price' => $activity->price,
                    ]);
                    if (!$activity->is_verification) {
                        do {
                            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_BOTH);
                            if (Ticket::where(['activity_id' => $activity->id, 'lottery_code' => $code])->doesntExist()) {
                                $ticket->lottery_code = $code;
                                $ticket->verified_at = Carbon::now();
                                break;
                            }
                        } while (true);
                    }
                    $ticket->save();
                    $activity->update([
                        'extends->participates' => bcadd(intval($activity->extends['participates']) ?? 0, 1)
                    ]);
                    $activity->decrement('stocks');
                });
            }
            $apply->status = $request->get('status');
            $apply->remark = $request->get('remark');
            $apply->reviewer = Auth::id();
            $apply->reviewed_at = Carbon::now();
            $apply->save();
            $apply->user->notify(new ApplyPaid($activity));
        });
        return Response::success();
    }
}
