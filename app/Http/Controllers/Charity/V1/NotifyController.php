<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Jobs\BulkSMS;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class NotifyController extends Controller
{
    /**
     * @param Request $request
     * @param Activity $activity
     * @return JsonResponse|JsonResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function send(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        $request->validate([
            'content' => 'required|string|max:256',
        ]);
        $userIds = $activity->tickets()->get()->pluck('user_id');
        BulkSMS::dispatch($userIds, $request->get('content'));
        return Response::success();
    }
}
