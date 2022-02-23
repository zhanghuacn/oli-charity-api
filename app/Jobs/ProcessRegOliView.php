<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessRegOliView implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::asForm()->post(config('services.custom.oli_register_url'). '/login/isEmailExist', ['email' => $this->user->email]);
        $result = json_decode($response->body());
        $data = [
            'email' => $this->user->email,
            'username' => $this->user->name,
            'password' => Crypt::decryptString($this->user->password),
            'url' => config('app.url'),
        ];
        $response = Http::asForm()->post(config('services.custom.oli_register_url'), $data);
        Log::info(sprintf('响应参数：%s', $response->body()));
    }
}
