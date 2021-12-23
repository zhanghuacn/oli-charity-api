<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Request;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $data = Activity::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success($data);
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        return Response::success($activity);
    }

    public function audit(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'status' => 'required|in:PASSED,REFUSE',
            'remark' => 'sometime|string',
        ]);
        $activity->update($request->all());
        return Response::success();
    }
}
