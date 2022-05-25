<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Sponsor;
use App\Models\Ticket;
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
//        Gate::authorize('check-ticket', $activity);
        $data = $activity->lotteries()->get()->map(function ($item) use ($activity) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'image' => collect($item->images)->first(),
                'time' => $item->draw_time,
                'standard_amount' => floatval($item->standard_amount),
                'standard_oli_register' => $item->extends['standard_oli_register'] ?? false,
                'is_standard_oli_register' => Auth::user()->sync ?? false,
                'is_standard' => $activity->my_ticket != null && floatval(optional($activity->my_ticket)->amount) >= floatval($item->standard_amount),
                'lottery_code' => $activity->my_ticket != null && floatval(optional($activity->my_ticket)->amount) >= floatval($item->standard_amount) ? optional($activity->my_ticket)->lottery_code : null,
                'prizes' => $item->prizes->transform(function (Prize $prize) {
                    return [
                        'id' => $prize->id,
                        'name' => $prize->name,
                        'stock' => $prize->num,
                        'price' => floatval($prize->price),
                        'sponsor' => optional($prize->prizeable)->getMorphClass() != Sponsor::class ? [] : [
                            'id' => $prize->prizeable->id,
                            'name' => $prize->prizeable->name,
                            'logo' => $prize->prizeable->logo,
                        ],
                        'images' => $prize->images,
                        'description' => $prize->description,
                        'winners' => $prize->winners,
                    ];
                }),
            ];
        });
        return Response::success($data);
    }

    public function qualification(Activity $activity, Lottery $lottery): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $data = [
            'id' => $lottery->id,
            'name' => $lottery->name,
            'description' => $lottery->description,
            'image' => collect($lottery->images)->first(),
            'time' => $lottery->draw_time,
            'standard_amount' => floatval($lottery->standard_amount),
            'standard_oli_register' => $lottery->extends['standard_oli_register'] ?? false,
            'is_standard_oli_register' => Auth::user()->sync ?? false,
            'is_standard' => floatval(optional($activity->my_ticket)->amount) >= floatval($lottery->standard_amount),
            'lottery_code' => floatval(optional($activity->my_ticket)->amount) >= floatval($lottery->standard_amount) ? optional($activity->my_ticket)->lottery_code : ''
        ];
        return Response::success($data);
    }

    public function show(Activity $activity, Lottery $lottery): JsonResponse|JsonResource
    {
        //Gate::authorize('check-ticket', $activity);
        $data = array_merge(
            $lottery->toArray(),
            [
                'lottery_code' => Auth::check() ?
                    (floatval(optional($activity->my_ticket)->amount) >= floatval($lottery->standard_amount) ? optional($activity->my_ticket)->lottery_code : '') : '',
                'is_standard' => $activity->my_ticket != null && floatval($activity->my_ticket->amount) >= floatval($lottery->standard_amount),
                'difference' => Auth::check() ? (floatval($lottery->standard_amount) > floatval(optional($activity->my_ticket)->amount) ?
                    floatval($lottery->standard_amount) - floatval(optional($activity->my_ticket)->amount) : 0) : $lottery->standard_amount,
                'winner' => $lottery->prizes()->whereJsonContains('winners', ['id' => Auth::id()])->first(['id', 'name']),
            ]
        );
        $data['prizes'] = $lottery->prizes->map(function (Prize $prize) {
            $prize->winners = array_map(function ($item) use ($prize) {
                $ticket = Ticket::where(['activity_id' => $prize->activity_id, 'user_id' => $item['id']])->first();
                $item['lottery_code'] = $ticket->lottery_code;
                $item['sponsor'] = optional($prize->prizeable)->getMorphClass() != Sponsor::class ? [] : [
                    'id' => $prize->prizeable->id,
                    'name' => $prize->prizeable->name,
                    'logo' => $prize->prizeable->logo,
                ];
                return $item;
            }, $prize->winners->toArray());
            return $prize;
        })->toArray();
        return Response::success($data);
    }
}
