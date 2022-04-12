<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class PaymentMethodController extends Controller
{
    /**
     * 获取支付方法
     *
     * @param Request $request
     * @return JsonResponse|JsonResource
     */
    public function index(Request $request): JsonResponse|JsonResource
    {
        return Response::success(Auth::user()->paymentMethods());
    }

    /**
     * 添加支付方式
     *
     * @param Request $request
     * @return JsonResponse|JsonResource
     */
    public function store(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);
        Auth::user()->addPaymentMethod($request->get('payment_method'));
        return Response::success();
    }

    /**
     * 获取指定支付方法.
     *
     * @param Request $request
     * @return JsonResponse|JsonResource
     */
    public function show(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);
        $paymentMethod = Auth::user()->findPaymentMethod($request->get('payment_method'));
        return Response::success($paymentMethod);
    }

    /**
     * 更新默认支付方式.
     *
     * @param Request $request
     * @return JsonResponse|JsonResource
     */
    public function update(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);
        Auth::user()->updateDefaultPaymentMethod($request->get('payment_method'));
        Auth::user()->updateDefaultPaymentMethodFromStripe();
        return Response::success();
    }

    /**
     * 删除支付方式.
     *
     * @param Request $request
     * @return JsonResponse|JsonResource
     */
    public function destroy(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);
        Auth::user()->findPaymentMethod($request->get('payment_method'))->delete();
        return Response::success();
    }

    /**
     * 获取默认支付方式
     * @param Request $request
     * @return JsonResponse|JsonResource
     */
    public function default(Request $request): JsonResponse|JsonResource
    {
        return Response::success(Auth::user()->defaultPaymentMethod());
    }

    /**
     * 更新默认支付方式.
     *
     * @param Request $request
     * @return JsonResponse|JsonResource
     */
    public function createSetupIntent(Request $request): JsonResponse|JsonResource
    {
        return Response::success([
            'intent' => Auth::user()->createSetupIntent()
        ]);
    }
}
