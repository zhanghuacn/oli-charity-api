<?php

namespace App\Jobs;

use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\LotteryPaid;
use Aws\Sns\SnsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessLotteryWinner implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private SnsClient $snsClient;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SnsClient $snsClient)
    {
        $this->snsClient = $snsClient;
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
            Lottery::where('status', '=', false)->where('draw_time', '<=', Carbon::now())->whereNotNull('draw_time')->get()
                ->each(function (Lottery $lottery) {
                    $data = $lottery->activity->tickets()->where([['amount', '>=', $lottery->standard_amount], ['type', '=', Ticket::TYPE_DONOR]]);
                    if ($lottery->extends['standard_oli_register']) {
                        $data->whereHas('user', function ($query) {
                            $query->where('sync', '=', true);
                        });
                    }
                    if ($data->exists()) {
                        $tickets = $data->get()->pluck('amount', 'user_id')->toArray();
                        Log::info('tickets:' . json_encode($tickets));
                        $money = collect($tickets)->values();
                        $ids = array_keys($tickets);
                        $n = $lottery->prizes()->sum('num');
                        if (!empty($money) && !empty($ids) && !empty($n)) {
                            $data = ['money' => $money, 'ids' => $ids, 'n' => $n, 'min_requirement' => $lottery->standard_amount];
                            $body = Http::post(config('services.custom.lottery_url'), $data)->body();
                            $result = json_decode($body, true);
                            if (is_array($result)) {
                                $start = 0;
                                $lottery->prizes()->each(function (Prize $prize) use (&$start, $result) {
                                    $ids = collect($result)->slice($start, $prize->num);
                                    if (!empty($ids)) {
                                        $users = User::whereIn('id', $ids)->select('id', 'name', 'avatar', 'email', 'phone', 'first_name', 'last_name')->get();
                                        Log::info(json_encode(['winners' => $users->toArray()]));
                                        $prize->update(['winners' => $users->toArray()]);
                                        foreach ($users as $user) {
                                            $user->notify(new LotteryPaid($prize, $user));
                                            if (!empty($user->phone)) {
                                                $this->smsPublish($prize, $user);
                                            }
                                        }
                                    }
                                    $start += $prize->num;
                                });
                            } else {
                                Log::error('Lottery algorithm exception');
                            }
                        } else {
                            Log::error('Abnormal lottery conditions');
                        }
                    } else {
                        Log::error('Too few participants in the lottery');
                    }
                    $lottery->update(['status' => true]);
                });
        });
    }

    private function smsPublish(Prize $prize, User $user): void
    {
        $event = $prize->activity->name;
        $prize = $prize->name;
        $name = $user->name;
        $date = Carbon::parse($prize->activity->end_time)->toFormattedDateString();
        $message = <<<EOF
Dear $name
Congratulations, you've won the $prize in our $event,
You can claim your prize on the day of the banquet on $date.
If you have any questions, please contact the administrator of the WeChat group and check the details by email.
EOF;
        $this->snsClient->publish([
            'Message' => $message,
            'PhoneNumber' => sprintf('+%s', $user->phone),
            'MessageAttributes' => [
                'AWS.SNS.SMS.SMSType' => [
                    'DataType' => 'String',
                    'StringValue' => 'Transactional',
                ]
            ],
        ]);
    }
}
