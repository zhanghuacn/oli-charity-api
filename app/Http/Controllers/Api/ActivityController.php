<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $paginate = Activity::orderByDesc('id')->simplePaginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($paginate));
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        visits($activity)->increment();
        return Response::success(new ActivityResource($activity));
    }

    public function subscribe(Activity $activity): JsonResponse|JsonResource
    {
        Auth::user()->subscribe($activity);
        return Response::success();
    }

    public function unsubscribe(Activity $activity): JsonResponse|JsonResource
    {
        try {
            Auth::user()->unsubscribe($activity);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }
}
