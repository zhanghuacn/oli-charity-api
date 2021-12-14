<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LotteryCollection;
use App\Http\Resources\Api\LotteryResource;
use App\Models\Activity;
use App\Models\Lottery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function React\Promise\map;

class LotteryController extends Controller
{
    public function index(Activity $activity): JsonResponse|JsonResource
    {

        $tickets = $activity->tickets()->where(['user_id' => Auth::id()])->firstOrFail();
        $data = $activity->lotteries()->get()->map(function ($item) use ($tickets) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'image' => collect($item->images)->first(),
                'time' => $item->draw_time,
                'standard_amount' => $item->standard_amount,
                'is_standard' => $tickets->amount >= $item->standard_amount,
            ];
        });
        return Response::success($data);
    }

    public function show(Activity $activity, Lottery $lottery): JsonResponse|JsonResource
    {
        $tickets = $activity->tickets()->where(['user_id' => Auth::id()])->firstOrFail();
        $data = array_merge($lottery->toArray(), ['prizes' => $lottery->prizes, 'lottery_code' => $tickets->lottery_code]);
        return Response::success($data);
    }
}
