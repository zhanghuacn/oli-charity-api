<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BazaarCollection;
use App\Http\Resources\Api\WarehouseCollection;
use App\Models\Activity;
use App\Models\Bazaar;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class BazaarController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $data = Bazaar::filter($request->all())->where(['user_id' => Auth::id()])->paginate($request->input('per_page', 15));
        return Response::success(new BazaarCollection($data));
    }

    public function warehouse(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-staff', $activity);
        $data = Bazaar::filter($request->all())->where([])->paginate($request->input('per_page', 15));
        return Response::success(new WarehouseCollection($data));
    }

    public function affirm(Request $request, Bazaar $bazaar): JsonResponse|JsonResource
    {
        $request->validate([
            'remark' => 'sometimes|string|nullable',
        ]);
        abort_if($bazaar->is_receive, 422, 'Please do not repeat the confirmation');
        $bazaar->is_receive = true;
        $bazaar->save();
        return Response::success();
    }
}
