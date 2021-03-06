<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Charity\RoleCollection;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Jiannei\Response\Laravel\Support\Facades\Response;

class RoleController extends Controller
{
    public function __construct()
    {
//        $this->authorizeResource(Role::class, 'role');
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $roles = Role::filter($request->all())->with('permissions')->paginate($request->input('per_page', 15));
        return Response::success(new RoleCollection($roles));
    }

    public function show(Role $role): JsonResponse|JsonResource
    {
        return Response::success($role);
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('roles')
                    ->where(function ($query) use ($request) {
                        return $query->where([
                            'name' => $request->get('name'),
                            'guard_name' => Auth::getDefaultDriver(),
                        ]);
                    })
            ],
            'permissions' => 'sometimes|array|exists:permissions,name'
        ]);
        $role = Role::create($request->only(['name']));
        $role->syncPermissions($request->get('permissions'));
        return Response::success();
    }

    public function update(Request $request, Role $role): JsonResponse|JsonResource
    {
        $request->validate([
            'name' => [
                'sometimes',
                Rule::unique('roles')
                    ->where(function ($query) use ($request) {
                        return $query->where([
                            'team_id' => getPermissionsTeamId(),
                            'name' => $request->get('name'),
                            'guard_name' => Auth::getDefaultDriver(),
                        ]);
                    })->ignore($role)
            ],
            'permissions' => 'sometimes|array|exists:permissions,name'
        ]);
        if ($request->has('name')) {
            $role->update(['name' => $request->get('name')]);
        }
        $role->syncPermissions($request->get('permissions'));
        return Response::success($role);
    }

    public function destroy(Role $role): JsonResponse|JsonResource
    {
        $role->delete();
        return Response::success();
    }
}
