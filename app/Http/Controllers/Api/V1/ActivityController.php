<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\UserCollection;
use App\Models\Activity;
use App\Models\Apply;
use App\Models\GroupInvite;
use App\Models\Order;
use App\Models\Ticket;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function abort;
use function abort_if;
use function visits;

class ActivityController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'filter' => 'sometimes|string|in:CURRENT,UPCOMING,PAST',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $activities = Activity::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        $data['hosts'] = new UserCollection($activity->tickets()->with('user')
            ->where('type', '=', Ticket::TYPE_STAFF)
            ->whereJsonContains('extends->host', Ticket::ROLE_HOST)->get()
            ->map(function ($staff) {
                return $staff->user;
            }));
        $data['is_follow'] = Auth::check() && $activity->hasBeenFavoritedBy(Auth::user());
        $data['specialty'] = $activity->getExtends()['specialty'];
        $data['timeline'] = $activity->getExtends()['timeline'];
        $data['charity'] = [
            'id' => $activity->charity->id,
            'name' => $activity->charity->name,
            'logo' => $activity->charity->logo,
        ];
        $data['price'] = $activity->price;
        if (Auth::check()) {
            if ($activity->is_private) {
                $activityApplyRecord = $activity->applies()->where(['user_id' => Auth::id(), 'status' => Apply::STATUS_PASSED])->first();
                if ($activityApplyRecord->exists()) {
                    $data['apply_status'] = $activityApplyRecord->status;
                }
            }
            $teamInvite = GroupInvite::whereTicketId($activity->ticket()->id)->first();
            if ($teamInvite) {
                $data['invite'] = [
                    'inviter_id' => $teamInvite->inviter->id,
                    'inviter_name' => $teamInvite->inviter->name,
                    'inviter_avatar' => $teamInvite->inviter->avatar,
                    'team_name' => $teamInvite->team->name,
                    'accept_token' => $teamInvite->accept_token,
                    'deny_token' => $teamInvite->deny_token,
                ];
            }
            $data['role'] = $activity->ticket()->type;
        }
        visits($activity)->increment();
        return Response::success(array_merge($activity->toArray(), $data));
    }

    public function personRanks(Activity $activity): JsonResponse|JsonResource
    {
        abort_if(!$activity->ticket()->exists, 403, 'Permission denied');
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
        abort_if(!$activity->ticket()->exists, 403, 'Permission denied');
        $ranks = $activity->tickets()->select('table_num', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('table_num')->orderByDesc('total_amount')->get();
        return Response::success($ranks);
    }

    public function teamRanks(Activity $activity): JsonResponse|JsonResource
    {
        abort_if(!$activity->ticket()->exists, 403, 'Permission denied');
        $ranks = $activity->tickets()->with('group')->whereNotNull('group_id')
            ->select('group_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('group_id')->get()->map(function ($item) {
                return [
                    'id' => $item->group->id,
                    'name' => $item->group->name,
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
            ->orderByDesc('payment_time')->get(['payment_type', 'amount', 'payment_time', 'payment_status'])
            ->transform(function ($item) {
                return [
                    'type' => $item->payment_type,
                    'amount' => $item->amount,
                    'time' => $item->payment_time,
                    'status' => $item->payment_status,
                ];
            });
        return Response::success([
            'rank' => $ranks->ranks,
            'total_amount' => $ranks->amount,
            'records' => $orders,
        ]);
    }

    public function order(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'method' => 'sometimes|in:STRIPE',
            'amount' => 'required|numeric|min:1|not_in:0',
        ]);
        abort_if(empty($activity->charity->stripe_account), 500, 'No stripe connect account opened');
        $order = $this->orderService->activity(Auth::user(), $activity, $request->amount);
        return Response::success([
            'order_sn' => $order->order_sn,
            'client_secret' => $order->extends['client_secret']
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
