<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Models\Activity;
use App\Models\ActivityApplyRecord;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $activities = Activity::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        $data['hosts'] = $activity->tickets()->with('user')
            ->where('type', Ticket::TYPE_STAFF)->get()
            ->map(function ($staff) {
                return $staff->user;
            });
        $data['is_follow'] = Auth::check() && $activity->isSubscribedBy(Auth::user());
        if ($activity->is_private && Auth::check()) {
            $activityApplyRecord = $activity->applies()->where(['user_id' => Auth::id(), 'status' => ActivityApplyRecord::STATUS_PASSED])->first();
            if ($activityApplyRecord->exists()) {
                $data['apply_status'] = $activityApplyRecord->status;
            }
        }
        visits($activity)->increment();
        return Response::success(array_merge($activity->toArray(), $data));
    }

    public function personRanks(Activity $activity): JsonResponse|JsonResource
    {
        abort_if($activity->tickets()->where('user_id', Auth::id())->doesntExist(), 403, 'Permission denied');
        $ranks = $activity->tickets()->with('user')->orderByDesc('amount')->get()
            ->map(function ($item) {
                return [
                    'id' => $item->user->id,
                    'name' => $item->user->name,
                    'avatar' => $item->user->avatar,
                    'amount' => $item->amount,
                ];
            });
        return Response::success($ranks);
    }

    public function tableRanks(Activity $activity): JsonResponse|JsonResource
    {
        abort_if($activity->tickets()->where('user_id', Auth::id())->doesntExist(), 403, 'Permission denied');
        $ranks = $activity->tickets()->select('table_num', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('table_num')->orderByDesc('total_amount')->get();
        return Response::success($ranks);
    }

    public function teamRanks(Activity $activity): JsonResponse|JsonResource
    {
        abort_if($activity->tickets()->where('user_id', Auth::id())->doesntExist(), 403, 'Permission denied');
        $ranks = $activity->tickets()->with('team')->select('team_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('team_id')->get()->map(function ($item) {
                return [
                    'id' => $item->team->id,
                    'name' => $item->team->name,
                    'total_amount' => $item->total_amount,
                ];
            });
        return Response::success($ranks);
    }

    public function anonymous(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'enable' => 'required|boolean',
        ]);
        $activity->tickets()->where(['user_id' => Auth::id()])->firstOrFail()->update(['anonymous' => $request['enable']]);
        return Response::success();
    }

    public function subscribe(Activity $activity): JsonResponse|JsonResource
    {
        Auth::user()->subscribe($activity);
        return Response::success();
    }

    public function unsubscribe(Activity $activity): JsonResponse|JsonResource
    {
        try {
            Auth::user()->unsubscribe($activity);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }
}
