<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Models\Activity;
use App\Models\ActivityApplyRecord;
use App\Models\Order;
use App\Models\TeamInvite;
use App\Models\Ticket;
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
        $data['is_follow'] = Auth::check() && $activity->hasBeenFavoritedBy(Auth::user());
        if (Auth::check()) {
            if ($activity->is_private) {
                $activityApplyRecord = $activity->applies()->where(['user_id' => Auth::id(), 'status' => ActivityApplyRecord::STATUS_PASSED])->first();
                if ($activityApplyRecord->exists()) {
                    $data['apply_status'] = $activityApplyRecord->status;
                }
            }
            $teamInvite = TeamInvite::whereTicketId($activity->currentTicket()->id)->first();
            if ($teamInvite->exists()) {
                $data['invite'] = [
                    'inviter_id' => $teamInvite->inviter->id,
                    'inviter_name' => $teamInvite->inviter->name,
                    'inviter_avatar' => $teamInvite->inviter->avatar,
                    'team_name' => $teamInvite->team->name,
                    'accept_token' => $teamInvite->accept_token,
                    'deny_token' => $teamInvite->deny_token,
                ];
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

    public function history(Activity $activity): JsonResponse|JsonResource
    {
        $ranks = $activity->tickets()->selectRaw('user_id, amount, (RANK() OVER(ORDER BY amount DESC)) as ranks')->get()
            ->firstWhere('user_id', '=', Auth::id());
        $orders = $activity->orders()->where(['user_id' => Auth::id(), 'payment_status' => Order::STATUS_PAID])
            ->orderByDesc('payment_time')->get(['payment_type', 'amount', 'payment_time']);
        return Response::success([
            'rank' => $ranks->ranks,
            'total_amount' => $ranks->amount,
            'records' => $orders,
        ]);
    }

    public function favorite(Activity $activity): JsonResponse|JsonResource
    {
        Auth::user()->favorite($activity);
        return Response::success();
    }

    public function unfavorite(Activity $activity): JsonResponse|JsonResource
    {
        try {
            Auth::user()->unfavorite($activity);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }
}
