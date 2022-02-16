<?php

namespace App\Jobs;

use App\Models\Lottery;
use App\Models\Prize;
use App\Models\User;
use App\Notifications\LotteryPaid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\Logger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LotteryWinners implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Lottery $lottery;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Lottery $lottery)
    {
        $this->lottery = $lottery;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        DB::transaction(function () {
            $tickets = $this->lottery->activity->tickets()->where('amount', '>=', $this->lottery->standard_amount)
                ->pluck('amount', 'user_id')->toArray();
            $num = $this->lottery->prizes()->sum('num');
            $money = collect($tickets)->values()->map(function ($item) {
                return floatval($item);
            });
            $data = ['money' => $money, 'ids' => array_keys($tickets), 'n' => min($num, count(array_keys($tickets)))];
            Log::info(sprintf('请求参数：%s', json_encode($data)));
            $response = Http::post(config('services.custom.lottery_url'), $data);
            $result = json_decode($response->body());
            Log::info(sprintf('响应参数：%s', $response->body()));
            $start = 0;
            $this->lottery->prizes()->each(function (Prize $prize) use (&$start, $result) {
                $ids = array_slice($result, $start, $prize->num);
                if (!empty($ids)) {
                    $users = User::whereIn('id', $ids)->get(['id', 'name', 'avatar']);
                    $prize->update(['winners' => $users->toArray()]);
                    $start += $start == 0 ? $prize->num - 1 : $prize->num;
                    foreach ($users as $user) {
                        $user->notify(new LotteryPaid($prize));
                    }
                }
            });
            $this->lottery->update(['status' => true]);
        });
    }
}
