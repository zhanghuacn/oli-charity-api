<?php

namespace App\Http\Controllers\Sponsor\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Gift;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class GiftController extends Controller
{
    public function index(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'keyword' => 'sometimes|string',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = $activity->gifts()->whereHasMorph('giftable', [Sponsor::class], function (Builder $query) {
            $query->where('giftable_id', '=', getPermissionsTeamId());
        })->filter($request->all())->paginate($request->input('per_page', 15));
        return Response::success($data);
    }

    public function users(Request $request, Activity $activity, Gift $gift)
    {
        $request->validate([
            'username' => 'sometimes|string',
            'email' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        abort_if($gift->activity_id != $activity->id, 403, 'Permission denied');
        abort_if(get_class($gift->giftable) != Sponsor::class || $gift->giftable->id != getPermissionsTeamId(), 403, 'Permission denied');
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
