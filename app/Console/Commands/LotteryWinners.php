<?php

namespace App\Console\Commands;

use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\LotteryPaid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        DB::transaction(function () {
            Lottery::where('status', '<>', true)->where('draw_time', '<=', now())->get()->each(function (Lottery $lottery) {
                $result = $lottery->activity->tickets()->where('amount', '>=', $lottery->standard_amount)->where(['type' => Ticket::TYPE_DONOR]);
                abort_if($result->doesntExist(), 500, 'Too few participants in the lottery');
                $tickets = $result->pluck('amount', 'user_id')->toArray();
                Log::info('tickets:' . json_encode($tickets));
                $money = collect($tickets)->values();
                $ids = array_keys($tickets);
                $n = min($lottery->prizes()->sum('num'), count(array_keys($tickets)));
                abort_if(empty($money) || empty($ids) || empty($n), 500, 'Abnormal lottery conditions');
                $data = ['money' => $money, 'ids' => $ids, 'n' => $n];
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
        });
    }
}
