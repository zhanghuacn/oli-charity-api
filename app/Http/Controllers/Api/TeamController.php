<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Team;
use App\Models\TeamInvite;
use App\Models\Ticket;
use App\Notifications\InviteToTeam;
use App\Services\TeamService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Throwable;

class TeamController extends Controller
{
    private TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function search(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'keyword' => 'required',
        ]);
        $users = $activity->tickets()->whereNull('current_team_id')
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
                ];
            });
        return Response::success($users);
    }

    /**
     * @throws Throwable
     */
    public function store(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $team = DB::transaction(function () use ($activity, $request) {
            $ticket = $activity->tickets()->where(['user_id' => Auth::id()])->firstOrFail();
            abort_if(!empty($ticket->current_team_id), 422, 'Joined the team');
            $request->validate([
                'name' => 'required|string',
                'description' => 'sometimes|string',
                'num' => 'sometimes|numeric|min:1|not_in:0'
            ]);
            $team = Team::create(array_merge(
                $request->only(['name', 'description', 'num']),
                [
                    'charity_id' => $activity->charity_id,
                    'activity_id' => $activity->id,
                    'owner_id' => Auth::id(),
                ]
            ));
            $ticket->current_team_id = $team->id;
            $ticket->save();
            $team->tickets()->attach($ticket->id);
            return $team;
        });

        return Response::success($team);
    }

    public function update(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $ticket = $activity->tickets()->where(['user_id' => Auth::id()])->firstOrFail();
        abort_if(empty($ticket->current_team_id), 422, 'Not joined any team');
        $request->validate([
            'name' => 'required|string',
            'description' => 'sometimes|string',
            'num' => 'sometimes|numeric|min:1|not_in:0'
        ]);
        $ticket->currentTeam()->update($request->only(['name', 'description', 'num']));
        return Response::success();
    }

    public function invite(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'ticket' => 'required|exists:tickets,code',
        ]);
        $ticket = Ticket::whereCode($request->ticket)->first();
        if (!$this->teamService->hasPendingInvite($ticket, $activity->currentTicket()->currentTeam)) {
            $this->teamService->inviteToTeam($ticket, $activity->currentTicket()->currentTeam, function (TeamInvite $invite) {
                $invite->ticket->user->notify(new InviteToTeam($invite));
            });
        }
        return Response::success();
    }

    public function acceptInvite(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'accept_token' => 'required',
        ]);
        $invite = TeamInvite::whereAcceptToken($request->accept_token)->firstOrFail();
        $this->teamService->acceptInvite($invite);
        return Response::success();
    }

    public function denyInvite(Activity $activity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'deny_token' => 'required',
        ]);
        $invite = TeamInvite::whereDenyToken($request->deny_token)->firstOrFail();
        $this->teamService->denyInvite($invite);
        return Response::success();
    }

    public function quit(Activity $activity): JsonResponse|JsonResource
    {
        $ticket = $activity->currentTicket();
        $ticket->detachTeam($ticket->current_team_id);
        $ticket->update([
            'current_team_id' => null,
        ]);
        return Response::success();
    }
}
