<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $permissions = Permission::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success($permissions);
    }

    public function show(Permission $permission): JsonResponse|JsonResource
    {
        Gate::authorize('check-admin-driver', $permission);
        return Response::success($permission);
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);
        return Response::success(Permission::create($request->all()));
    }

    public function update(Request $request, Permission $permission): JsonResponse|JsonResource
    {
        Gate::authorize('check-admin-driver', $permission);
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);
        $permission->update($request->all());
        return Response::success($permission);
    }

    public function destroy(Permission $permission): JsonResponse|JsonResource
    {
        Gate::authorize('check-admin-driver', $permission);
        $permission->delete();
        return Response::success();
    }
}
