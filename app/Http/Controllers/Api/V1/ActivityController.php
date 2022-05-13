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
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
            'filter' => 'sometimes|string|in:CURRENT,UPCOMING,PAST,NOT_CURRENT',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $request->merge(['is_visible' => true]);
        $activities = Activity::filter($request->all())->paginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        $data['hosts'] = new UserCollection($activity->tickets()->with('user')
            ->whereIn('type', [Ticket::TYPE_STAFF, Ticket::TYPE_HOST])->get()
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
            'events' => $activity->charity->activities_count ?? 0,
            'donations' => optional($activity->charity->setting)->donations ?? 0,
            'members' => $activity->charity->favoriters()->count(),
        ];
        $data['price'] = floatval($activity->price);
        $data['is_sales'] = $activity->stocks - $activity->extends['participates'] > 0;
        if (Auth::check()) {
            if ($activity->is_private) {
                $activityApplyRecord = $activity->applies()->where(['user_id' => Auth::id()])->first();
                $data['apply_status'] = match (optional($activityApplyRecord)->status) {
                    Apply::STATUS_WAIT => 'APPLYINT',
                    Apply::STATUS_PASSED => 'APPLIED',
                    Apply::STATUS_REFUSE => 'REFUSE',
                    default => 'WAIT'
                };
            }
            $data['is_buy'] = !empty($activity->my_ticket);
            $data['is_albums'] = $activity->extends['is_albums'];
            if ($activity->my_ticket) {
                $groupInvite = GroupInvite::whereTicketId($activity->my_ticket->id)->first();
                if ($groupInvite) {
                    $data['invite'] = [
                        'inviter_id' => optional($groupInvite->inviter)->id,
                        'inviter_name' => optional($groupInvite->inviter)->name,
                        'inviter_avatar' => optional($groupInvite->inviter)->avatar,
                        'team_name' => optional($groupInvite->group)->name,
                        'accept_token' => $groupInvite->accept_token,
                        'deny_token' => $groupInvite->deny_token,
                    ];
                }
                $data['role'] = match ($activity->my_ticket->type) {
                    TICKET::TYPE_DONOR => TICKET::TYPE_DONOR,
                    TICKET::TYPE_SPONSOR => TICKET::TYPE_SPONSOR,
                    default => TICKET::TYPE_CHARITY,
                };
                $data['is_anonymous'] = $activity->my_ticket->anonymous;
                $data['is_group'] = !empty($activity->my_ticket->group);
                if (!$activity->is_verification) {
                    $activity->my_ticket->update(['verified_at' => now()]);
                }
                $data['is_sign'] = $activity->is_verification == false && !empty($activity->my_ticket->verified_at);
            }
        }
        visits($activity)->increment();
        return Response::success(array_merge($activity->toArray(), $data));
    }

    public function apply(Activity $activity): JsonResponse|JsonResource
    {
        abort_if(!$activity->is_private, 403, 'Permission denied');
        $apply = $activity->applies()->firstOrCreate([
            'charity_id' => $activity->charity_id,
            'user_id' => Auth::id(),
        ]);
        abort_if($apply->status == Apply::STATUS_REFUSE, 403, 'Permission denied');
        return Response::success();
    }

    public function personRanks(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $ranks = $activity->tickets()->whereNotIn('type', [Ticket::TYPE_HOST, Ticket::TYPE_STAFF])
            ->with('user')->orderByDesc('amount')->get()
            ->filter(function (Ticket $ticket) {
                return floatval($ticket->amount) > 0;
            })
            ->map(function (Ticket $ticket) {
                return [
                    'id' => $ticket->user->id,
                    'name' => $ticket->anonymous ? 'anonymous' : $ticket->user->name,
                    'avatar' => $ticket->anonymous ? null : $ticket->user->avatar,
                    'total' => floatval($ticket->amount),
                ];
            });
        return Response::success($ranks);
    }

    public function tableRanks(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $ranks = $activity->tickets()->select('seat_num', DB::raw('SUM(amount) as total'))->whereNotNull('seat_num')
            ->groupBy('seat_num')->orderByDesc('total')->get();
        return Response::success($ranks);
    }

    public function teamRanks(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $ranks = $activity->tickets()->with('group')->whereNotNull('group_id')
            ->select('group_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('group_id')->get()->map(function ($item) {
                return [
                    'id' => $item->group->id,
                    'name' => $item->group->name,
                    'total' => floatval($item->total_amount),
                ];
            });
        return Response::success($ranks);
    }

    public function history(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $ranks = $activity->tickets()->selectRaw('user_id, amount, (RANK() OVER(ORDER BY amount DESC)) as ranks')->get()
            ->firstWhere('user_id', '=', Auth::id());
        $orders = $activity->orders()->where(['user_id' => Auth::id(), 'type' => Order::TYPE_ACTIVITY, 'payment_status' => Order::STATUS_PAID])
            ->orderByDesc('payment_time')->get(['payment_type', 'amount', 'payment_time', 'payment_status'])
            ->transform(function ($item) {
                return [
                    'type' => $item->payment_type,
                    'amount' => floatval($item->amount),
                    'time' => Carbon::parse($item->payment_time)->format('Y-m-d H:i:s'),
                    'status' => $item->payment_status,
                ];
            });
        return Response::success([
            'rank' => optional($ranks)->ranks,
            'total_amount' => floatval(optional($ranks)->amount),
            'records' => $orders,
        ]);
    }

    public function order(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'payment_method' => 'nullable|string',
            'amount' => 'required|numeric|min:1|not_in:0',
        ]);
        abort_if(empty($activity->charity->stripe_account_id), 500, 'No stripe connect account opened');
        abort_if(Carbon::parse($activity->end_time)->lt(Carbon::now()), 422, 'Event ended');
        $order = $this->orderService->activity(Auth::user(), $activity, $request->get('amount'), $request->payment_method);
        return Response::success([
            'stripe_account_id' => $activity->charity->stripe_account_id,
            'order_sn' => $order->order_sn,
            'client_secret' => $order->extends['client_secret']
        ]);
    }

    public function favorite(Activity $activity): JsonResponse|JsonResource
    {
        if (!Auth()->user()->hasFavorited($activity)) {
            Auth::user()->favorite($activity);
        }
        return Response::success();
    }

    public function unfavorite(Activity $activity): JsonResponse|JsonResource
    {
        try {
            if (Auth()->user()->hasFavorited($activity)) {
                Auth::user()->unfavorite($activity);
            }
        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }
}
