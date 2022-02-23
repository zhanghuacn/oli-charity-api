<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Charity\ApplyCollection;
use App\Models\Activity;
use App\Models\Apply;
use App\Notifications\ApplyPaid;
use App\Notifications\LotteryPaid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
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
        $applies = $activity->applies()->filter($request->all())->with('user:id,name,avatar,profile')->paginate($request->input('per_page', 15));
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
        $apply->status = $request->get('status');
        $apply->remark = $request->get('remark');
        $apply->reviewer = Auth::id();
        $apply->reviewed_at = Carbon::tz(config('app.timezone'))->now();
        $apply->save();
        $apply->user->notify(new ApplyPaid($activity));
        return Response::success();
    }
}
