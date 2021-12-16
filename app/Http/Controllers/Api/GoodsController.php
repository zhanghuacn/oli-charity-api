<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GoodsCollection;
use App\Http\Resources\Api\GoodsResource;
use App\Models\Activity;
use App\Models\Goods;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class GoodsController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    public function index(Activity $activity): JsonResponse|JsonResource
    {
        abort_if($activity->tickets()->where('user_id', Auth::id())->doesntExist(), 403, 'Permission denied');
        return Response::success(new GoodsCollection($activity->goods));
    }

    public function show(Activity $activity, Goods $goods): JsonResponse|JsonResource
    {
        abort_if($activity->tickets()->where('user_id', Auth::id())->doesntExist(), 403, 'Permission denied');
        return Response::success(new GoodsResource($goods));
    }

    public function order(Activity $activity, Goods $goods, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'method' => 'required|in:STRIPE',
        ]);
        $order = $this->orderService->bazaar(Auth::user(), $activity->charity, $goods);
        return Response::success([
            'order_id' => $order->order_sn,
            'client_secret' => $order->extends['client_secret']
        ]);
    }
}
