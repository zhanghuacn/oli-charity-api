<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GiftResource;
use App\Models\Activity;
use App\Models\Gift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class GiftController extends Controller
{
    public function index(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        return Response::success($activity->gifts->transform(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'image' => collect($item->images)->first(),
                'description' => $item->description,
                'is_like' => $item->isLikedBy(Auth::user()),
                'sponsor' => [
                    'id' => optional($item->giftable)->id,
                    'name' => optional($item->giftable)->name,
                    'logo' => optional($item->giftable)->logo,
                ],
            ];
        }));
    }

    public function show(Activity $activity, Gift $gift): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        abort_if($activity->gifts()->where(['id' => $gift->id])->doesntExist(), 404);
        return Response::success(new GiftResource($gift));
    }

    public function like(Activity $activity, Gift $gift): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        abort_if(Auth::user()->phone == null, 422, 'Please verify your phone');
        if (!Auth::user()->hasLiked($gift)) {
            Auth::user()->like($gift);
        }
        return Response::success();
    }
}
