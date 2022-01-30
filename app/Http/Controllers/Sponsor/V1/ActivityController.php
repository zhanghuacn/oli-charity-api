<?php

namespace App\Http\Controllers\Sponsor\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sponsor\ActivityCollection;
use App\Http\Resources\Sponsor\ActivityResource;
use App\Models\Activity;
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
            })->simplePaginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        return Response::success(new ActivityResource($activity));
    }

    public function update(Request $request, Activity $activity): JsonResponse|JsonResource
    {
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
            'sales.*.content' => 'sometimes|string',
            'sales.*.stock' => 'required|integer|min:1|not_in:0',
            'sales.*.price' => 'required|numeric|min:0|not_in:0',
            'sales.*.images' => 'required|array',
            'sales.*.images.*' => 'required|url',
        ]);
        if (!empty($request->get('prizes'))) {
            $activity->prizes()->upsert($request->get('prizes'), ['id'], ['name', 'description', 'stock', 'price', 'images']);
        }
        if (!empty($request->get('sales'))) {
            $activity->goods()->upsert($request->get('sales'), ['id'], ['name', 'description', 'content', 'stock', 'price', 'images']);
        }
        return Response::success();
    }
}
