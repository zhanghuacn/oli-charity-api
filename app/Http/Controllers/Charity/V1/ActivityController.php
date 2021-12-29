<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Charity\ActivityCollection;
use App\Http\Resources\Charity\ActivityResource;
use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ActivityController extends Controller
{
    private ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->authorizeResource(Activity::class, 'activity');
        $this->activityService = $activityService;
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'filter' => 'sometimes|in:ACTIVE,PAST',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $activities = Activity::withCount(['applies', 'tickets'])->filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $this->checkStore($request);
        $this->activityService->create($request);
        return Response::success();
    }

    public function show(Activity $activity): JsonResponse|JsonResource
    {
        return Response::success(new ActivityResource($activity));
    }

    public function update(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $this->checkUpdate($request);
        $this->activityService->update($activity, $request);
        return Response::success();
    }

    public function destroy(Activity $activity): JsonResponse|JsonResource
    {
        return Response::success();
    }

    /**
     * @param Request $request
     * @return void
     */
    private function checkStore(Request $request): void
    {
        $request->validate([
            'basic.name' => 'required|string',
            'basic.description' => 'required|string',
            'basic.content' => 'required|string',
            'basic.location' => 'required|string',
            'basic.begin_time' => 'required|date_format:Y-m-d H:i:s',
            'basic.end_time' => 'required|date_format:Y-m-d H:i:s',
            'basic.price' => 'required|numeric|min:0|not_in:0',
            'basic.stock' => 'required|integer|min:1|not_in:0',
            'basic.is_private' => 'required|boolean',
            'basic.images' => 'required|array',
            'basic.specialty' => 'sometimes|array',
            'basic.specialty.*.title' => 'required|string',
            'basic.specialty.*.description' => 'required|string',
            'basic.timeline' => 'sometimes|array',
            'basic.timeline.*.time' => 'required|date',
            'basic.timeline.*.title' => 'required|string',
            'basic.timeline.*.description' => 'required|string',
            'lotteries' => 'sometimes|array',
            'lotteries.*.name' => 'required|string',
            'lotteries.*.description' => 'required|string',
            'lotteries.*.begin_time' => 'required|date_format:Y-m-d H:i:s',
            'lotteries.*.end_time' => 'required|date_format:Y-m-d H:i:s',
            'lotteries.*.standard_amount' => 'required|numeric|min:0|not_in:0',
            'lotteries.*.type' => 'required|in:AUTOMATIC,MANUAL',
            'lotteries.*.draw_time' => 'exclude_unless:type,true|required|date_format:Y-m-d H:i:s',
            'lotteries.*.images' => 'required|array',
            'lotteries.*.images.*' => 'required|url',
            'lotteries.*.prizes' => 'sometimes|array',
            'lotteries.*.prizes.*.name' => 'required|string',
            'lotteries.*.prizes.*.description' => 'required|string',
            'lotteries.*.prizes.*.stock' => 'required|integer|min:1|not_in:0',
            'lotteries.*.prizes.*.price' => 'required|numeric|min:0|not_in:0',
            'lotteries.*.prizes.*.content' => 'sometimes|string',
            'lotteries.*.prizes.*.images' => 'required|array',
            'lotteries.*.prizes.*.images.*' => 'required|url',
            'lotteries.*.prizes.*.sponsor' => 'sometimes',
            'lotteries.*.prizes.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales' => 'sometimes|array',
            'sales.*.name' => 'required|string',
            'sales.*.description' => 'required|string',
            'sales.*.stock' => 'required|integer|min:1|not_in:0',
            'sales.*.price' => 'required|numeric|min:0|not_in:0',
            'sales.*.content' => 'sometimes|string',
            'sales.*.sponsor' => 'sometimes',
            'sales.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales.*.images' => 'required|array',
            'sales.*.images.*' => 'required|url',
            'staffs' => 'sometimes|array',
            'staffs.*.type' => 'required|in:HOST,STAFF',
            'staffs.*.user_id' => 'required|distinct|integer|exists:charity_user,user_id',
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    private function checkUpdate(Request $request): void
    {
        $request->validate([
            'basic.id' => 'required|integer|exists:activities,id',
            'basic.name' => 'required|string',
            'basic.description' => 'required|string',
            'basic.content' => 'required|string',
            'basic.location' => 'required|string',
            'basic.begin_time' => 'required|date_format:Y-m-d H:i:s',
            'basic.end_time' => 'required|date_format:Y-m-d H:i:s',
            'basic.price' => 'required|numeric|min:0|not_in:0',
            'basic.stock' => 'required|integer|min:1|not_in:0',
            'basic.is_private' => 'required|boolean',
            'basic.images' => 'required|array',
            'basic.specialty' => 'sometimes|array',
            'basic.specialty.*.title' => 'required|string',
            'basic.specialty.*.description' => 'required|string',
            'basic.timeline' => 'sometimes|array',
            'basic.timeline.*.time' => 'required|date',
            'basic.timeline.*.title' => 'required|string',
            'basic.timeline.*.description' => 'required|string',
            'lotteries' => 'sometimes|array',
            'lotteries.*.id' => 'required|integer|exists:lotteries,id',
            'lotteries.*.name' => 'required|string',
            'lotteries.*.description' => 'required|string',
            'lotteries.*.begin_time' => 'required|date_format:Y-m-d H:i:s',
            'lotteries.*.end_time' => 'required|date_format:Y-m-d H:i:s',
            'lotteries.*.standard_amount' => 'required|numeric|min:0|not_in:0',
            'lotteries.*.type' => 'required|in:AUTOMATIC,MANUAL',
            'lotteries.*.draw_time' => 'exclude_unless:type,true|required|date_format:Y-m-d H:i:s',
            'lotteries.*.images' => 'required|array',
            'lotteries.*.images.*' => 'required|url',
            'lotteries.*.prizes' => 'sometimes|array',
            'lotteries.*.prizes.*.id' => 'required|integer|exists:prizes,id',
            'lotteries.*.prizes.*.name' => 'required|string',
            'lotteries.*.prizes.*.description' => 'required|string',
            'lotteries.*.prizes.*.stock' => 'required|integer|min:1|not_in:0',
            'lotteries.*.prizes.*.price' => 'required|numeric|min:0|not_in:0',
            'lotteries.*.prizes.*.content' => 'sometimes|string',
            'lotteries.*.prizes.*.images' => 'required|array',
            'lotteries.*.prizes.*.images.*' => 'required|url',
            'lotteries.*.prizes.*.sponsor' => 'sometimes',
            'lotteries.*.prizes.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales' => 'sometimes|array',
            'sales.*.id' => 'required|integer|exists:goods,id',
            'sales.*.name' => 'required|string',
            'sales.*.description' => 'required|string',
            'sales.*.stock' => 'required|integer|min:1|not_in:0',
            'sales.*.price' => 'required|numeric|min:0|not_in:0',
            'sales.*.content' => 'sometimes|string',
            'sales.*.sponsor' => 'sometimes',
            'sales.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales.*.images' => 'required|array',
            'sales.*.images.*' => 'required|url',
            'staffs' => 'sometimes|array',
            'staffs.*.type' => 'required|in:HOST,STAFF',
            'staffs.*.user_id' => 'required|distinct|integer|exists:charity_user,user_id',
        ]);
    }
}
