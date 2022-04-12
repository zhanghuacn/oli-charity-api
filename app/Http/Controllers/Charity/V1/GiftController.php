<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Gift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class GiftController extends Controller
{
    public function index(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        $request->validate([
            'keyword' => 'sometimes|string',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = $activity->gifts()->filter($request->all())->paginate($request->input('per_page', 15));
        return Response::success($data);
    }

    public function users(Request $request, Activity $activity, Gift $gift)
    {
        Gate::authorize('check-charity-source', $activity);
        $request->validate([
            'username' => 'sometimes|string',
            'email' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = $gift->likers()->filter($request->all())->paginate($request->input('per_page', 15));
        $data->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'username' => $item->username,
                'avatar' => $item->avatar,
                'email' => $item->email,
                'phone' => $item->phone,
                'first_name' => $item->first_name,
                'middle_name' => $item->middle_name,
                'last_name' => $item->last_name,
            ];
        });
        return Response::success($data);
    }
}
