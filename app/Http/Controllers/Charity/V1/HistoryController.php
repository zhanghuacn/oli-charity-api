<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class HistoryController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'type' => 'sometimes|string|in:TICKETS,CHARITY,BAZAAR,ACTIVITY',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $request->merge(['charity_id' => getPermissionsTeamId()]);
        $data = Order::filter($request->all())->with('user')->paginate($request->input('per_page', 15));
        $data->getCollection()->transform(function ($model) {
            return [
                'order_sn' => $model->order_sn,
                'type' => $model->type,
                'currency' => $model->currency,
                'username' => optional($model->user)->username,
                'avatar' => optional($model->user)->avatar,
                'total_amount' => $model->total_amount,
                'payment_type' => $model->payment_type,
                'payment_method' => $model->payment_method,
                'payment_status' => $model->payment_status,
                'payment_time' => Carbon::parse($model->payment_time)->tz(config('app.timezone'))->format('Y-m-d H:i:s'),
                'created_at' => Carbon::parse($model->created_at)->tz(config('app.timezone'))->format('Y-m-d H:i:s'),
            ];
        });
        return Response::success($data);
    }
}
