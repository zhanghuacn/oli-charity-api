<?php

namespace App\Http\Controllers\Sponsor\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Charity\StaffCollection;
use App\Models\Charity;
use App\Models\Role;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class StaffController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Sponsor::findOrFail(getPermissionsTeamId())->staffs()->simplePaginate($request->input('per_page', 15));
        return Response::success(new StaffCollection($data));
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required|string'
        ]);
        $user = User::whereUsername($request->get('username'))->orWhere('email', $request->get('username'))->first();
        abort_if($user->sponsors()->where('id', getPermissionsTeamId())->exists(), 422, 'Joined Sponsor');
        $user->sponsors()->attach(getPermissionsTeamId());
        return Response::success();
    }

    public function destroy(User $user): JsonResponse|JsonResource
    {
        abort_if(!$user->sponsors->pluck('id')->contains(getPermissionsTeamId()), 403);
        abort_if($user->hasRole(Role::ROLE_CHARITY_SUPER_ADMIN, Sponsor::GUARD_NAME), 403);
        $sponsor = Sponsor::findOrFail(getPermissionsTeamId());
        $sponsor->staffs()->detach($user->id);
        return Response::success();
    }
}
