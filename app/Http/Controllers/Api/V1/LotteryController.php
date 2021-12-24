<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LotteryCollection;
use App\Http\Resources\Api\LotteryResource;
use App\Models\Activity;
use App\Models\Lottery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function collect;

class LotteryController extends Controller
{
    public function index(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $data = $activity->lotteries()->get()->map(function ($item) use ($activity) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'image' => collect($item->images)->first(),
                'time' => $item->draw_time,
                'standard_amount' => $item->standard_amount,
                'is_standard' => $activity->ticket()->amount >= $item->standard_amount,
            ];
        });
        return Response::success($data);
    }

    public function show(Activity $activity, Lottery $lottery): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $data = array_merge($lottery->toArray(), ['prizes' => $lottery->prizes, 'lottery_code' => $activity->ticket()->lottery_code]);
        return Response::success($data);
    }
}
