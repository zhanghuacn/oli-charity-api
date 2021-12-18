<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\CharityCollection;
use App\Http\Resources\Api\NotificationCollection;
use App\Http\Resources\Api\UserCollection;
use App\Models\Activity;
use App\Models\Charity;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class UcenterController extends Controller
{
    public function notifications(Request $request): JsonResponse|JsonResource
    {
        $data = Auth::user()->notifications()->simplePaginate($request->input('per_page', 15));
        return Response::success(new NotificationCollection($data));
    }

    public function information(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'avatar' => 'sometimes|url',
            'first_name' => 'sometimes|string',
            'middle_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'birthday' => 'sometimes|date',
            'name' => 'sometimes|string',
            'profile' => 'sometimes|string',
        ]);
        Auth::user()->update($request->only(['avatar', 'first_name', 'middle_name', 'last_name', 'birthday', 'name', 'profile']));
        Auth::user()->refresh();
        return Response::success();
    }

    public function privacy(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'portfolio' => 'required|boolean',
            'records' => 'required|boolean',
        ]);
        Auth::user()->update(['settings->portfolio' => $request['portfolio'], 'settings->records' => $request['records']]);
        Auth::user()->refresh();
        return Response::success(Auth::user()->settings);
    }

    public function activities(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'filter' => 'sometimes|in:CURRENT,UPCOMING,PAST',
        ]);
        $request->merge(['user_id' => Auth::id()]);
        $activities = Activity::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    public function chart(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'year' => 'sometimes|date_format:"Y"',
        ]);
        $request->merge([
            'user_id' => Auth::id(),
            'payment_status' => Order::STATUS_PAID,
        ]);
        $data['total_amount'] = Order::filter($request->all())->sum('amount');
        $received = Order::filter($request->all())->selectRaw('DATE_FORMAT(payment_time, "%m") as date, sum(amount) as total_amount')
            ->groupBy('date')->pluck('total_amount', 'date')->toArray();
        for ($i = 1; $i <= 12; $i++) {
            $data['received'][$i] = array_key_exists($i, $received) ? $received[strval($i)] : 0;
        }
        return Response::success($data);
    }

    public function history(Request $request): JsonResponse|JsonResource
    {
        $request->merge([
            'user_id' => Auth::id(),
            'payment_status' => Order::STATUS_PAID,
        ]);
        $orders = Order::filter($request->all())->simplePaginate($request->input('per_page', 10));
        $orders->getCollection()->transform(function (Order $order) {
            return [
                'id' => $order->order_sn,
                'amount' => $order->amount,
                'time' => $order->payment_time,
                'orderable' => [
                    'id' => $order->orderable->id,
                    'name' => $order->orderable->name,
                    'type' => $order->type,
                ],
            ];
        });
        return Response::success($orders);
    }

    public function followCharities(Request $request): JsonResponse|JsonResource
    {
        $data = Auth::user()->subscriptions()->withType(Charity::class)
            ->with('subscribable')->simplePaginate($request->input('per_page', 15));
        $data->getCollection()->transform(function ($model) {
            return $model->subscribable;
        });
        return Response::success(new CharityCollection($data));
    }

    public function followActivities(Request $request): JsonResponse|JsonResource
    {
        $data = Auth::user()->subscriptions()->withType(Activity::class)
            ->with('subscribable')->simplePaginate($request->input('per_page', 15));
        $data->getCollection()->transform(function ($model) {
            return $model->subscribable;
        });
        return Response::success(new ActivityCollection($data));
    }

    public function followUsers(Request $request): JsonResponse|JsonResource
    {
        $data = Auth::user()->followings()->simplePaginate($request->input('per_page', 15));
        return Response::success(new UserCollection($data));
    }
}
