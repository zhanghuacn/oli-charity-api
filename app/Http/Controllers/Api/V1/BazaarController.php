<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BazaarCollection;
use App\Http\Resources\Api\WarehouseCollection;
use App\Models\Activity;
use App\Models\Bazaar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class BazaarController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'charity_id' => 'sometimes|integer|exists:charities,id',
            'activity_id' => 'sometimes|integer|exists:activities,id',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Bazaar::filter($request->all())->where(['user_id' => Auth::id()])->paginate($request->input('per_page', 15));
        return Response::success(new BazaarCollection($data));
    }

    public function warehouse(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        Gate::authorize('check-staff', $activity);
        $data = Bazaar::filter($request->all())->where(['activity_id' => $activity->id])->paginate($request->input('per_page', 15));
        return Response::success(new WarehouseCollection($data));
    }

    public function affirm(Request $request, Bazaar $bazaar): JsonResponse|JsonResource
    {
        $request->validate([
            'remark' => 'nullable|string',
        ]);
        abort_if($bazaar->is_receive, 422, 'Please do not repeat the confirmation');
        $bazaar->is_receive = true;
        $bazaar->save();
        return Response::success();
    }
}
