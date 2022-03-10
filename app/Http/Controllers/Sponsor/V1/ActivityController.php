<?php

namespace App\Http\Controllers\Sponsor\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sponsor\ActivityCollection;
use App\Http\Resources\Sponsor\ActivityResource;
use App\Models\Activity;
use App\Models\Gift;
use App\Models\Goods;
use App\Models\Prize;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'keyword' => 'sometimes|string',
            'filter' => 'sometimes|in:ACTIVE,PAST',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $activities = Activity::filter()
            ->whereHas('prizes', function (Builder $query) {
                $query->whereHasMorph('prizeable', Sponsor::class, function (Builder $query) {
                    $query->where('id', '=', getPermissionsTeamId());
                });
            })
            ->orWhereHas('goods', function (Builder $query) {
                $query->whereHasMorph('goodsable', Sponsor::class, function (Builder $query) {
                    $query->where('id', '=', getPermissionsTeamId());
                });
            })
            ->orWhereHas('gifts', function (Builder $query) {
                $query->whereHasMorph('giftable', Sponsor::class, function (Builder $query) {
                    $query->where('id', '=', getPermissionsTeamId());
                });
            })->paginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        if ($activity->status == Activity::STATUS_PASSED) {
            return Response::success(new ActivityResource($activity));
        } else {
            $result = $activity->cache;
            if (!empty($activity->cache['lotteries'])) {
                $result['lotteries'] = collect($activity->cache['lotteries'])->map(function ($lottery) {
                    $lottery['prizes'] = collect($lottery['prizes'])->filter(function ($value) {
                        return !empty($value['sponsor']) && $value['sponsor']['id'] == getPermissionsTeamId();
                    })->all();
                    return $lottery;
                })->values();
            }
            if (!empty($activity->cache['sales'])) {
                $result['sales'] = collect($activity->cache['sales'])->filter(function ($value) {
                    return !empty($value['sponsor']) && $value['sponsor']['id'] == getPermissionsTeamId();
                })->values();
            }
            if (!empty($activity->cache['gifts'])) {
                $result['gifts'] = collect($activity->cache['gifts'])->filter(function ($value) {
                    return !empty($value['sponsor']) && $value['sponsor']['id'] == getPermissionsTeamId();
                })->values();
            }
            return Response::success($result);
        }
    }

    public function update(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        abort_if($activity->status == Activity::STATUS_REVIEW, 422, 'During review, please do not submit again');
        $request->validate([
            'prizes' => 'sometimes|array',
            'prizes.*.id' => 'required|exists:prizes,id,prizeable_type,' . Sponsor::class,
            'prizes.*.name' => 'required|string',
            'prizes.*.description' => 'sometimes|string',
            'prizes.*.stock' => 'required|integer|min:1|not_in:0',
            'prizes.*.price' => 'required|numeric|min:0|not_in:0',
            'prizes.*.images' => 'required|array',
            'prizes.*.images.*' => 'required|url',
            'sales' => 'sometimes|array',
            'sales.*.id' => 'required|exists:goods,id,goodsable_type,' . Sponsor::class,
            'sales.*.name' => 'required|string',
            'sales.*.description' => 'sometimes|string',
            'sales.*.content' => 'nullable|string',
            'sales.*.stock' => 'required|integer|min:1|not_in:0',
            'sales.*.price' => 'required|numeric|min:0|not_in:0',
            'sales.*.images' => 'required|array',
            'sales.*.images.*' => 'required|url',
            'gifts' => 'sometimes|array',
            'gifts.*.id' => 'required|exists:gifts,id,giftable_type,' . Sponsor::class,
            'gifts.*.name' => 'required|string',
            'gifts.*.description' => 'sometimes|string',
            'gifts.*.content' => 'nullable|string',
            'gifts.*.images' => 'required|array',
            'gifts.*.images.*' => 'required|url',
        ]);
        $sponsor = Sponsor::find(getPermissionsTeamId());
        $data = $activity->cache;
        if (!empty($request->get('prizes'))) {
            $data['lotteries'] = collect($activity->cache['lotteries'])->map(function ($lottery) use ($sponsor, $request) {
                $lottery['prizes'] = collect($lottery['prizes'])->map(function ($value) use ($sponsor, $request) {
                    $result = collect($request->get('prizes'))->where('id', '=', $value['id'])->first() ?? $value;
                    if (!array_key_exists('sponsor', $result)) {
                        $result['sponsor'] = [
                            'id' => getPermissionsTeamId(),
                            'name' => $sponsor->name
                        ];
                    }
                    return $result;
                })->toArray();
                return $lottery;
            })->toArray();
        }
        if (!empty($request->get('sales'))) {
            $data['sales'] = collect($activity->cache['sales'])->map(function ($value) use ($sponsor, $request) {
                $result = collect($request->get('sales'))->where('id', '=', $value['id'])->first() ?? $value;
                if (!array_key_exists('sponsor', $result)) {
                    $result['sponsor'] = [
                        'id' => getPermissionsTeamId(),
                        'name' => $sponsor->name
                    ];
                }
                return $result;
            })->toArray();
        }
        if (!empty($request->get('gifts'))) {
            $data['gifts'] = collect($activity->cache['gifts'])->map(function ($value) use ($sponsor, $request) {
                $result = collect($request->get('gifts'))->where('id', '=', $value['id'])->first() ?? $value;
                if (!array_key_exists('sponsor', $result)) {
                    $result['sponsor'] = [
                        'id' => getPermissionsTeamId(),
                        'name' => $sponsor->name
                    ];
                }
                return $result;
            })->toArray();
        }
        $activity->cache = $data->toArray();
        $activity->status = Activity::STATUS_REVIEW;
        $activity->save();
        return Response::success();
    }
}
