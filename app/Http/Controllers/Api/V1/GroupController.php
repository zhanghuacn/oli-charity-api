<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\Ticket;
use App\Notifications\InvitePaid;
use App\Services\TeamService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Throwable;
use function abort_if;
use function optional;

class GroupController extends Controller
{
    private TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $ticket = $activity->ticket();
        abort_if(empty($ticket->group_id), 422, 'Not joined any team');
        $ranks = $activity->tickets()
            ->selectRaw('group_id, sum(amount) as total_amount, (RANK() OVER(ORDER BY sum(amount) DESC)) as ranks')
            ->whereNotNull('group_id')
            ->groupBy('group_id')->get()
            ->firstWhere('group_id', '=', $ticket->group_id);
        $data = [
            'id' => $ticket->group->id,
            'name' => $ticket->group->name,
            'description' => $ticket->group->description,
            'num' => $ticket->group->num,
            'rank' => $ranks->ranks,
            'seat_num' => $ticket->seat_num,
            'total_amount' => $ranks->total_amount,
            'members' => Ticket::wheregroupId($ticket->group_id)->with('user')->get()
                ->transform(function ($item) {
                    return [
                        'id' => $item->user->id,
                        'name' => $item->user->name,
                        'avatar' => $item->user->avatar,
                        'profile' => $item->user->profile,
                        'total_amount' => $item->amount,
                    ];
                }),
            'invite' => GroupInvite::whereTeamId($ticket->group_id)->with('ticket.user')->get()
                ->transform(function ($item) {
                    return [
                        'id' => $item->ticket->user->id,
                        'name' => $item->ticket->user->name,
                        'avatar' => $item->ticket->user->avatar,
                        'profile' => $item->ticket->user->profile,
                        'total_amount' => $item->ticket->amount,
                    ];
                })
        ];
        return Response::success($data);
    }

    public function search(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $request->validate([
            'keyword' => 'required',
        ]);
        $data = $activity->tickets()->whereNull('group_id')
            ->whereHas('user', function (Builder $query) use ($request) {
                $query->where('username', 'like', $request->keyword . '%')
                    ->orWhere('email', 'like', $request->keyword . '%');
            })->get()->transform(function ($item) {
                return [
                    'id' => $item->user_id,
                    'name' => optional($item->user)->name,
                    'avatar' => optional($item->user)->avatar,
                    'profile' => optional($item->user)->profile,
                    'ticket' => $item->code,
                    'is_invite' => GroupInvite::whereTicketId($item->id)->exists()
                ];
            });
        return Response::success($data);
    }

    /**
     * @throws Throwable
     */
    public function store(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $team = DB::transaction(function () use ($activity, $request) {
            $ticket = $activity->ticket();
            abort_if(!empty($ticket->group_id), 422, 'Joined The Group');
            abort_if(GroupInvite::whereTicketId($ticket->id)->exists(), 422, 'Invitation In Progress');
            $request->validate([
                'name' => 'required|string',
                'description' => 'sometimes|string',
                'num' => 'sometimes|numeric|min:1|not_in:0'
            ]);
            $team = Group::create(array_merge(
                $request->only(['name', 'description', 'num']),
                [
                    'charity_id' => $activity->charity_id,
                    'activity_id' => $activity->id,
                    'owner_id' => Auth::id(),
                ]
            ));
            $ticket->group_id = $team->id;
            $ticket->save();
            $team->tickets()->attach($ticket->id);
            return $team;
        });

        return Response::success($team);
    }

    public function update(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        Gate::authorize('check-group', $activity);
        $ticket = $activity->tickets()->where(['user_id' => Auth::id()])->firstOrFail();
        $request->validate([
            'name' => 'required|string',
            'description' => 'sometimes|string',
            'num' => 'sometimes|numeric|min:1|not_in:0'
        ]);
        $ticket->group()->update($request->only(['name', 'description', 'num']));
        return Response::success();
    }

    public function invite(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $request->validate([
            'ticket' => 'required|exists:tickets,code',
        ]);
        $ticket = Ticket::whereCode($request->get('ticket'))->first();
        if (!$this->teamService->hasPendingInvite($ticket, $activity->ticket()->group)) {
            $this->teamService->inviteToGroup($ticket, $activity->ticket()->group, function (GroupInvite $invite) {
                $invite->ticket->user->notify(new InvitePaid($invite));
            });
        }
        return Response::success();
    }

    public function acceptInvite(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $request->validate([
            'accept_token' => 'required',
        ]);
        $invite = GroupInvite::whereAcceptToken($request->get('accept_token'))->firstOrFail();
        $this->teamService->acceptInvite($invite);
        return Response::success();
    }

    public function denyInvite(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $request->validate([
            'deny_token' => 'required',
        ]);
        $invite = GroupInvite::whereDenyToken($request->get('deny_token'))->firstOrFail();
        $this->teamService->denyInvite($invite);
        return Response::success();
    }

    public function quit(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        DB::transaction(function () use ($activity) {
            $ticket = $activity->ticket();
            if ($ticket->group->owner_id == $ticket->group_id) {
                $owner = Group::whereId($ticket->group_id)->tickets()->where('id', '<>', $ticket->id)->first();
                $ticket->group->owner_id = $owner->id;
                $ticket->group->save();
            }
            $ticket->detachGroup($ticket->group_id);
            $ticket->update([
                'group_id' => null,
            ]);
        });
        return Response::success();
    }
}
