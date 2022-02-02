<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ActivityCollection;
use App\Http\Resources\Admin\ActivityResource;
use App\Models\Activity;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ActivityController extends Controller
{
    private ActivityService $activityService;

    /**
     * @param ActivityService $activityService
     */
    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Activity::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($data));
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        return Response::success(new ActivityResource($activity));
    }

    public function details(Activity $activity): JsonResponse|JsonResource
    {
        $data = $activity->cache->toArray();
        $data['basic']['status'] = $activity->status;
        $data['basic']['state'] = $activity->state;
        if (!empty($data['staffs'])) {
            foreach ($data['staffs'] as &$staff) {
                $user = User::find($staff['uid']);
                $staff['name'] = $user->name;
                $staff['avatar'] = $user->avatar;
            }
        }
        return Response::success($data);
    }

    public function audit(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'status' => 'required|in:PASSED,REFUSE',
            'remark' => 'sometimes|string',
        ]);

        $activity->status = $request->get('status');
        $activity->remark = $request->get('remark');
        if ($activity->status == Activity::STATUS_PASSED) {
            $this->activityService->update($activity, $activity->cache->toArray());
            $activity->is_visible = true;
        }
        $activity->cache = (new ActivityResource($activity))->toArray($request);
        $activity->save();
        return Response::success();
    }
}
