<?php

namespace App\Jobs;

use App\Models\Lottery;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
     */
    public function handle()
    {
        DB::transaction(function () {
            $tickets = $this->lottery->activity->tickets()->where('amount', '>=', $this->lottery->standard_amount)
                ->pluck('amount', 'user_id')->toArray();
            $this->lottery->prizes()->each(function (Prize $prize) use (&$tickets) {
                if (count($tickets) > 0) {
                    $money = collect($tickets)->values()->map(function ($item) {
                        return floatval($item);
                    });
                    $ids = array_keys($tickets);
                    $data = ['money' => $money, 'ids' => $ids, 'n' => min($prize->num, count($ids))];
                    $response = Http::post('https://4omu1zxuba.execute-api.ap-southeast-2.amazonaws.com/default/lottery', $data);
                    $result = json_decode($response->body());
                    foreach ($result as $item) {
                        unset($tickets[$item]);
                    }
                    $users = User::whereIn('id', $result)->get(['id', 'name', 'avatar'])->toArray();
                    $prize->update(['winners' => $users]);
                }
            });
            $this->lottery->update(['status' => true]);
        });
    }
}
