<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\CharityCollection;
use App\Http\Resources\Api\CharityResource;
use App\Models\Activity;
use App\Models\Charity;
use App\Models\News;
use App\Models\Order;
use App\Models\User;
use App\Rules\Price;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function abort;
use function abort_if;
use function visits;

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
        $request->validate([
            'sort' => 'sometimes|string|in:AMOUNT,TIME',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $request->merge(['is_visible' => true]);
        $paginate = Charity::filter($request->all())->paginate($request->input('per_page', 15));
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
            'payment_method' => 'nullable|string',
            'amount' => ['required', 'numeric', new Price()],
        ]);
        abort_if(empty($charity->stripe_account_id), 500, 'No stripe connect account opened');
        $user = User::findOrFail(Auth::id());
        $order = $this->orderService->charity($user, $charity, $request->amount, $request->payment_method);
        return Response::success([
            'stripe_account_id' => $charity->stripe_account_id,
            'order_sn' => $order->order_sn,
            'client_secret' => $order->extends['client_secret'],
            'payment_method' => $order->extends['payment_method'] ?? null
        ]);
    }

    public function activities(Request $request, Charity $charity): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:AMOUNT,TIME',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $activities = $charity->activities()->where(['is_visible' => true])->paginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
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
        $received = Order::filter($request->all())
            ->selectRaw('DATE_FORMAT(payment_time, "%m") as date, sum(amount) as total_amount')
            ->groupBy('date')->pluck('total_amount', 'date')->toArray();
        for ($i = 1; $i <= 12; $i++) {
            $data['received'][] = $received[str_pad($i, 2, '0', STR_PAD_LEFT)] ?? 0;
        }
        return Response::success($data);
    }

    public function history(Charity $charity): JsonResponse|JsonResource
    {
        $data = Order::where(['payment_status' => Order::STATUS_PAID, 'charity_id' => $charity->id])
            ->orderByDesc('created_at')->limit(15)->get()
            ->transform(function (Order $order) {
                return [
                    'type' => $order->type,
                    'name' => Str::random(10),
                    'date' => Carbon::parse($order->payment_time)->toDateString(),
                    'amount' => floatval($order->amount) ?? 0,
                ];
            });
        return Response::success($data);
    }

    public function source(Charity $charity): JsonResponse|JsonResource
    {
        $data = Order::filter([
            'charity_id' => $charity->id,
            'payment_status' => Order::STATUS_PAID,
        ])->selectRaw('type, sum(amount) as total_amount')
            ->groupBy('type')->get()->toArray();

        return Response::success($data);
    }

    public function favorite(Charity $charity): JsonResponse|JsonResource
    {
        if (!Auth()->user()->hasFavorited($charity)) {
            Auth::user()->favorite($charity);
        }
        return Response::success();
    }

    public function unfavorite(Charity $charity): JsonResponse|JsonResource
    {
        try {
            if (Auth()->user()->hasFavorited($charity)) {
                Auth::user()->unfavorite($charity);
            }
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }

    public function news(Charity $charity): JsonResponse|JsonResource
    {
        return Response::success($charity->news()->get()->transform(function (News $news) {
            return [
                'id' => $news->id,
                'title' => $news->title,
                'image' => $news->thumb,
                'description' => $news->description,
                'time' => $news->published_at
            ];
        }));
    }
}
