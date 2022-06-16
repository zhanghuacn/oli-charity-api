<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\SmsNotify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkSMS implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $ids;
    private string $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $ids, string $content)
    {
        $this->ids = $ids;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!empty($this->ids)) {
            User::whereIn('id', $this->ids)->whereNotNull('phone')->get()->each(function (User $user) {
                $user->notify(new SmsNotify($this->content));
            });
        }
    }
}
