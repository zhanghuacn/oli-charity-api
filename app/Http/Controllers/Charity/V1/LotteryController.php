<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Jobs\LotteryWinners;
use App\Models\Activity;
use App\Models\Lottery;
use App\Policies\AdminPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class LotteryController extends Controller
{
    public function draw(Lottery $lottery): JsonResponse|JsonResource
    {
        abort_if($lottery->status, 422, 'Please do not repeat the lottery');
        LotteryWinners::dispatch($lottery);
        return Response::success();
    }
}
