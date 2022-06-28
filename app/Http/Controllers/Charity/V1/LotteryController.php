<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\LotteryPaid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Jiannei\Response\Laravel\Support\Facades\Response;

class LotteryController extends Controller
{
    public function draw(Lottery $lottery): JsonResponse|JsonResource
    {
        abort_if($lottery->status, 422, 'Please do not repeat the lottery');
        DB::transaction(function () use ($lottery) {
            $data = $lottery->activity->tickets()->where([
                ['amount', '>=', $lottery->standard_amount], ['type', '=', Ticket::TYPE_DONOR]
            ]);
            if ($lottery->extends['standard_oli_register']) {
                $data->whereHas('user', function ($query) {
                    $query->where('sync', '=', true);
                });
            }
            abort_if($data->doesntExist(), 500, 'Too few participants in the lottery');
            $tickets = $data->get()->pluck('amount', 'user_id')->toArray();
            $this->lottery($tickets, $lottery);
        });
        return Response::success();
    }

    public function appoint(Request $request, Lottery $lottery): JsonResponse|JsonResource
    {
        abort_if($lottery->status, 422, 'Please do not repeat the lottery');
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|int|exists:tickets,id,activity_id,' . $lottery->activity_id,
        ]);
        DB::transaction(function () use ($request, $lottery) {
            $tickets = Ticket::whereIn('id', $request->get('ids'))->get()->pluck('amount', 'user_id')->toArray();
            $this->lottery($tickets, $lottery);
        });
        return Response::success();
    }

    public function lottery(array $tickets, Lottery $lottery): void
    {
        Log::info('tickets:' . json_encode($tickets));
        $money = collect($tickets)->values();
        $ids = array_keys($tickets);
        $n = intval($lottery->prizes()->sum('num'));
        $param = ['money' => $money, 'ids' => $ids, 'n' => $n, 'min_requirement' => $lottery->standard_amount];
        Log::info('data:' . json_encode($param));
        abort_if(empty($money) || empty($ids) || empty($n), 500, 'Abnormal lottery conditions');
        $body = Http::post(config('services.custom.lottery_url'), $param)->body();
        $result = json_decode($body, true);
        abort_if(!is_array($result), 500, 'Lottery algorithm exception');
        $start = 0;
        $lottery->prizes()->each(function (Prize $prize) use (&$start, $result) {
            $ids = collect($result)->slice($start, $prize->num);
            if (!empty($ids)) {
                $users = User::whereIn('id', $ids)->get(['id', 'name', 'avatar', 'email', 'phone']);
                Log::info(json_encode(['winners' => $users->toArray()]));
                $prize->update(['winners' => $users->toArray()]);
                foreach ($users as $user) {
                    $user->notify(new LotteryPaid($prize, $user));
                }
            }
            $start += $prize->num;
        });
        $lottery->update(['status' => true]);
    }
}
