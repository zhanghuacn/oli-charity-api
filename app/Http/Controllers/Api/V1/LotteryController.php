<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LotteryCollection;
use App\Http\Resources\Api\LotteryResource;
use App\Models\Activity;
use App\Models\Lottery;
use App\Models\Prize;
use Illuminate\Database\Eloquent\Collection;
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
                'standard_amount' => floatval($item->standard_amount),
                'is_standard' => floatval($activity->my_ticket->amount) >= floatval($item->standard_amount),
                'lottery_code' => $activity->my_ticket->lottery_code
            ];
        });
        return Response::success($data);
    }

    public function show(Activity $activity, Lottery $lottery): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $data = array_merge(
            $lottery->toArray(),
            [
                'prizes' => $lottery->prizes,
                'lottery_code' => $activity->my_ticket->lottery_code,
                'winner' => $lottery->prizes()->whereJsonContains('winners', ['id' => Auth::id()])->first(['id', 'name']),
            ]
        );
        return Response::success($data);
    }
}
