<?php

namespace App\Jobs;

use App\Models\Lottery;
use App\Models\Ticket;
use App\Notifications\RemindPaid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProcessLotteryReminder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $days;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $days)
    {
        $this->days = $days;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Lottery::with('activity.tickets')->where(['status' => false])->whereRaw('DATEDIFF(draw_time, NOW()) = ' . $this->days)->get()->each(function (Lottery $lottery) {
            $result = $lottery->activity->tickets()->where([['amount', '<=', $lottery->standard_amount], ['type', '=', Ticket::TYPE_DONOR]]);
            if ($lottery->extends['standard_oli_register'] == true) {
                $result->orWhereHas('user', function ($query) {
                    $query->where('sync', '<>', true);
                });
            }
            if ($result->exists()) {
                $result->with('user')->get()->each(function (Ticket $ticket) use ($lottery) {
                    $ticket->user->notify(new RemindPaid($lottery->activity, $this->days));
                });
            }
        });
    }
}
