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
    protected $description = 'Command description';

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
     */
    public function handle()
    {
        DB::transaction(function () {
            Lottery::where('status', '<>', true)->where('draw_time', '<=', now())->get()->each(function (Lottery $lottery) {
                $tickets = $lottery->activity->tickets()->where('amount', '>=', $lottery->standard_amount)
                    ->pluck('amount', 'user_id')->toArray();
                $lottery->prizes()->each(function (Prize $prize) use (&$tickets) {
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
                        $users = User::whereIn('id', $result)->get(['id', 'name', 'avatar']);
                        $prize->update(['winners' => $users->toArray()]);
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
