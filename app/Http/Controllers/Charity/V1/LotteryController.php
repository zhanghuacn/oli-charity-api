<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Jobs\LotteryWinners;
use App\Models\Activity;
use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\LotteryPaid;
use App\Policies\AdminPolicy;
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
        abort_if(!Carbon::now()->tz(config('app.timezone'))->between($lottery->begin_time, $lottery->end_time), 403, 'Please draw during the lucky draw');
        DB::transaction(function () use ($lottery) {
            $result = $lottery->activity->tickets()->where('amount', '>=', $lottery->standard_amount)->where(['type' => Ticket::TYPE_DONOR]);
            abort_if($result->doesntExist(), 500, 'Too few participants in the lottery');
            $tickets = $result->pluck('amount', 'user_id')->toArray();
            Log::info('tickets:' . json_encode($tickets));
            $money = collect($tickets)->values();
            $ids = array_keys($tickets);
            $n = min($lottery->prizes()->sum('num'), count(array_keys($tickets)));
            $data = ['money' => $money, 'ids' => $ids, 'n' => $n];
            Log::info('data:' . json_encode($data));
            abort_if(empty($money) || empty($ids) || empty($n), 500, 'Abnormal lottery conditions');
            $body = Http::post(config('services.custom.lottery_url'), $data)->body();
            $result = json_decode($body, true);
            abort_if(!is_array($result), 500, 'Lottery algorithm exception');
            $start = 0;
            $lottery->prizes()->each(function (Prize $prize) use (&$start, $result) {
                $ids = collect($result)->slice($start, $prize->num);
                if (!empty($ids)) {
                    $users = User::whereIn('id', $ids)->get(['id', 'name', 'avatar']);
                    $prize->update(['winners' => $users->toArray()]);
                    foreach ($users as $user) {
                        $user->notify(new LotteryPaid($prize));
                    }
                }
                $start += $prize->num;
            });
            $lottery->update(['status' => true]);
        });
        return Response::success();
    }
}
