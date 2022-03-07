<?php

namespace App\Http\Controllers\Web;

use App\Models\Prize;
use App\Models\User;
use App\Notifications\LotteryPaid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class HomeController
{
    public function index(): JsonResponse|JsonResource
    {
        $user = User::find(1);
        $user->notify(new LotteryPaid(Prize::find(73)));
        return Response::noContent();
    }
}
