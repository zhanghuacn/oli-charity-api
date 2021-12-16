<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\CharityCollection;
use App\Http\Resources\Api\CharityResource;
use App\Models\Charity;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class CharityController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $paginate = Charity::orderByDesc('id')->simplePaginate($request->input('per_page', 15));
        return Response::success(new CharityCollection($paginate));
    }

    public function show(Charity $charity): JsonResponse|JsonResource
    {
        visits($charity)->increment();
        return Response::success(new CharityResource($charity));
    }

    public function order(Charity $charity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'method' => 'sometimes|in:STRIPE',
            'amount' => 'required|numeric|min:1|not_in:0',
        ]);
        abort_if(empty($charity->stripe_account), 500, 'No stripe connect account opened');
        $order = $this->orderService->charity(Auth::user(), $charity, $request->amount);
        return Response::success([
            'order_id' => $order->order_sn,
            'client_secret' => $order->extends['client_secret']
        ]);
    }

    public function activities(Charity $charity): JsonResponse|JsonResource
    {
        return Response::success(new ActivityCollection($charity->activities));
    }

    public function favorite(Charity $charity): JsonResponse|JsonResource
    {
        Auth::user()->favorite($charity);
        return Response::success();
    }

    public function unfavorite(Charity $charity): JsonResponse|JsonResource
    {
        try {
            Auth::user()->unfavorite($charity);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }
}
