<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Charity\StaffCollection;
use App\Models\Charity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;

class StaffController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Charity::findOrFail(getPermissionsTeamId())->staffs()->simplePaginate($request->input('per_page', 15));
        return Response::success(new StaffCollection($data));
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required|string'
        ]);
        $user = User::whereUsername($request->get('username'))->orWhere('email', $request->get('username'))->first();
        abort_if(DB::table('charity_user')->where('user_id', $user->id)->exists(), 422, 'Joined Charity');
        $user->charities()->attach(getPermissionsTeamId());
        return Response::success();
    }

    public function destroy(User $user): JsonResponse|JsonResource
    {
        abort_if(!$user->charities->pluck('id')->contains(getPermissionsTeamId()), 403, 'Permission denied');
        abort_if($user->hasRole(Role::ROLE_CHARITY_SUPER_ADMIN, Charity::GUARD_NAME), 403, 'Permission denied');
        $charity = Charity::findOrFail(getPermissionsTeamId());
        $charity->staffs()->detach($user->id);
        return Response::success();
    }
}
