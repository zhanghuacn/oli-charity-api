<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessRegOliView implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $data = [
            'email' => $this->user['email'],
            'username' => '匿名',
            'password' => $this->user['password'] ?? 888888,
            'phone' => $this->user['phone'],
            'url' => 'https://oliview2080',
        ];
        Log::info(sprintf('请求参数：%s', json_encode($data)));
        $body = Http::asForm()->post(config('services.custom.oli_api_url') . '/login/existUser', $data)->body();
        Log::info(sprintf('响应参数：%s', $body));
        $result = json_decode($body, true);
        if ($result['status'] == 1 && $result['data']['ischecklogin']) {
            if (!empty($this->user['email'])) {
                User::whereEmail($this->user['email'])->update(['sync' => true]);
            }
            if (!empty($this->user['phone'])) {
                User::wherePhone($this->user['phone'])->update(['sync' => true]);
            }
        }
    }
}
