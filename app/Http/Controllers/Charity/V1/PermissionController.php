<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class PermissionController extends Controller
{
    public function __construct()
    {
//        $this->authorizeResource(Permission::class, 'permission');
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $permissions = Permission::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success($permissions);
    }

    public function show(Permission $permission): JsonResponse|JsonResource
    {
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
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);
        $permission->update($request->all());
        return Response::success($permission);
    }

    public function destroy(Permission $permission): JsonResponse|JsonResource
    {
        $permission->delete();
        return Response::success();
    }
}
