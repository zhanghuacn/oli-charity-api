<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GoodsCollection;
use App\Http\Resources\Api\GoodsResource;
use App\Models\Activity;
use App\Models\Goods;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function abort_if;

class GoodsController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        return Response::success($activity->goods->transform(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'image' => collect($item->images)->first(),
                'description' => $item->description,
                'sponsor' => [
                    'id' => optional($item->goodsable)->id,
                    'name' => optional($item->goodsable)->name,
                    'logo' => optional($item->goodsable)->logo,
                ],
            ];
        }));
    }

    public function show(Activity $activity, Goods $goods): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        abort_if($activity->goods()->where(['id' => $goods->id])->doesntExist(), 404);
        return Response::success(new GoodsResource($goods));
    }

    public function order(Activity $activity, Goods $goods, Request $request): JsonResponse|JsonResource
    {
        abort_if(empty($activity->charity->stripe_account_id), 500, 'No stripe connect account opened');
        abort_if(Carbon::parse($activity->end_time)->lt(now()), 422, 'Event ended');
        abort_if($activity->goods()->where(['id' => $goods->id])->doesntExist(), 404, 'Goods is not found');
        abort_if($goods->stock <= 0, 422, 'Goods sell out');
        $request->validate([
            'method' => 'required|in:STRIPE',
        ]);
        $order = $this->orderService->bazaar(Auth::user(), $activity, $goods);
        return Response::success([
            'stripe_account_id' => $activity->charity->stripe_account_id,
            'order_sn' => $order->order_sn,
            'client_secret' => $order->extends['client_secret']
        ]);
    }
}
