<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\CharityCollection;
use App\Http\Resources\Api\CharityResource;
use App\Models\Charity;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function abort;
use function abort_if;
use function visits;

class CharityController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:AMOUNT,TIME',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $request->merge(['status' => Charity::STATUS_PASSED]);
        $paginate = Charity::filter($request->all())->simplePaginate($request->input('per_page', 15));
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
        abort_if(empty($charity->stripe_account_id), 500, 'No stripe connect account opened');
        $order = $this->orderService->charity(Auth::user(), $charity, $request->amount);
        return Response::success([
            'order_sn' => $order->order_sn,
            'client_secret' => $order->extends['client_secret']
        ]);
    }

    public function activities(Charity $charity): JsonResponse|JsonResource
    {
        return Response::success(new ActivityCollection($charity->activities));
    }

    public function chart(Charity $charity, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'year' => 'sometimes|date_format:"Y"',
        ]);
        $request->merge([
            'charity_id' => $charity->id,
            'payment_status' => Order::STATUS_PAID,
        ]);
        $data['total_amount'] = Order::filter($request->all())->sum('amount');
        $received = Order::filter($request->all())->selectRaw('DATE_FORMAT(payment_time, "%m") as date, sum(amount) as total_amount')
            ->groupBy('date')->pluck('total_amount', 'date')->toArray();
        $total = 0;
        for ($i = 1; $i <= 12; $i++) {
            $total += array_key_exists($i, $received) ? $received[strval($i)] : 0;
            $data['received'][] = $total;
        }
        return Response::success($data);
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
