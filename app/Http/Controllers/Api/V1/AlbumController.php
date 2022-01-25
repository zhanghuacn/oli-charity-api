<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AlbumCollection;
use App\Http\Resources\Api\NewsCollection;
use App\Models\Activity;
use App\Models\Album;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class AlbumController extends Controller
{
    public function index(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $request->merge(['activity_id' => $activity->id]);
        $paginate = $activity->albums()->where(['is_visible' => true])->orderBy('id', $request->get('sort'))->simplePaginate($request->input('per_page', 10));
        return Response::success(new AlbumCollection($paginate));
    }

    public function store(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        abort_if($activity->extends['is_albums'] == false && $activity->my_ticket->type == Ticket::TYPE_DONOR, 403, 'Permission denied');
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'sometimes|url',
        ]);
        $models = [];
        foreach ($request->get('paths') as $item) {
            $models[] = new Album(['path' => $item, 'user_id' => Auth::id()]);
        }
        $activity->albums()->saveMany($models);
        return Response::success();
    }

    public function destroy(Activity $activity, Album $album): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        abort_if($album->user_id != Auth::id(), 403, 'Permission denied');
        $album->delete();
        return Response::success();
    }
}
