<?php

namespace App\Console\Commands;

use App\Models\Lottery;
use App\Models\Prize;
use App\Models\User;
use App\Notifications\LotteryPaid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LotteryWinners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lottery:draw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'lottery draw';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Throwable
     */
    public function handle()
    {
        DB::transaction(function () {
            Lottery::where('status', '<>', true)->where('draw_time', '<=', now())->get()->each(function (Lottery $lottery) {
                $tickets = $lottery->activity->tickets()->where('amount', '>=', $lottery->standard_amount)
                    ->pluck('amount', 'user_id')->toArray();
                $num = $lottery->prizes()->sum('num');
                $money = collect($tickets)->values()->map(function ($item) {
                    return floatval($item);
                });
                $data = ['money' => $money, 'ids' => array_keys($tickets), 'n' => min($num, count(array_keys($tickets)))];
                $response = Http::post(config('services.custom.lottery_url'), $data);
                $result = json_decode($response->body());
                $start = 0;
                $lottery->prizes()->each(function (Prize $prize) use (&$start, $result) {
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
                $lottery->update(['status' => true]);
            });
        });
    }
}
