<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Jobs\LotteryWinners;
use App\Models\Activity;
use App\Models\Lottery;
use App\Models\Prize;
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
        DB::transaction(function () use ($lottery) {
            $tickets = $lottery->activity->tickets()->where('amount', '>=', $lottery->standard_amount)
                ->pluck('amount', 'user_id')->toArray();
            $num = $lottery->prizes()->sum('num');
            $money = collect($tickets)->values()->map(function ($item) {
                return floatval($item);
            });
            $data = ['money' => $money, 'ids' => array_keys($tickets), 'n' => min($num, count(array_keys($tickets)))];
            Log::info(sprintf('请求参数：%s', json_encode($data)));
            $response = Http::post(config('services.custom.lottery_url'), $data);
            $result = json_decode($response->body());
            $start = 0;
            $lottery->prizes()->each(function (Prize $prize) use (&$start, $result) {
                $ids = array_slice($result, $start, $prize->num);
                if (!empty($ids)) {
                    $users = User::whereIn('id', $ids)->get(['id', 'name', 'avatar']);
                    $prize->update(['winners' => $users->toArray()]);
                    $start += $start == 0 ? 1 : $prize->num;
                    foreach ($users as $user) {
                        $user->notify(new LotteryPaid($prize));
                    }
                }
            });
            $lottery->update(['status' => true]);
        });
        return Response::success();
    }
}
