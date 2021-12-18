<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class UserController extends Controller
{
    public function show(User $user): JsonResponse|JsonResource
    {
        if (Auth::check()) {
            $user = $user->attachFollowStatus(Auth::user());
        }
        return Response::success(new UserResource($user));
    }

    public function constitute(User $user, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'year' => 'sometimes|date_format:"Y"',
        ]);
        $request->merge([
            'user_id' => $user->id,
            'payment_status' => Order::STATUS_PAID,
        ]);
        $orders = Order::filter($request->all())->with('charity')->selectRaw('charity_id, sum(amount) as total_amount')
            ->groupBy('charity_id')->get()->transform(function (Order $order) {
                return [
                    'id' => $order->charity_id,
                    'name' => $order->charity->name,
                    'total_amount' => $order->total_amount
                ];
            });
        return Response::success($orders);
    }

    public function chart(User $user, Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'year' => 'sometimes|date_format:"Y"',
        ]);
        $request->merge([
            'user_id' => $user->id,
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

    public function follow(User $user): JsonResponse|JsonResource
    {
        Auth::user()->follow($user);
        return Response::success();
    }

    public function unfollow(User $user): JsonResponse|JsonResource
    {
        Auth::user()->unfollow($user);
        return Response::success();
    }
}
