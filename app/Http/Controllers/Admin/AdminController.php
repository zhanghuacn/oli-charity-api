<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Admin::class, 'admin');
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $admins = Admin::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success($admins);
    }

    public function show(Admin $admin): JsonResponse|JsonResource
    {
        $data = array_merge($admin->toArray(), ['roles' => $admin->getRoleNames()]);
        return Response::success($data);
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string',
            'username' => 'required|unique:admins,username',
            'avatar' => 'sometimes|string',
            'roles' => 'sometimes|array'
        ]);
        $admin = DB::transaction(function () use ($request) {
            $admin = Admin::create($request->only(['name', 'email', 'password', 'username', 'avatar']));
            $admin->syncRoles($request->get('roles'));
            return $admin;
        });
        $data = array_merge($admin->toArray(), ['roles' => $admin->getRoleNames()]);
        return Response::success($data);
    }

    public function update(Request $request, Admin $admin): JsonResponse|JsonResource
    {
        $request->validate([
            'name' => 'sometime|string',
            'password' => 'sometime|string',
            'avatar' => 'sometimes|string',
            'roles' => 'sometimes|array'
        ]);
        DB::transaction(function () use ($request, $admin) {
            $admin->update($request->only(['name', 'password', 'avatar']));
            $admin->syncRoles($request->get('roles'));
        });
        $data = array_merge($admin->toArray(), ['roles' => $admin->getRoleNames()]);
        return Response::success($data);
    }

    public function destroy(Admin $admin): JsonResponse|JsonResource
    {
        $admin->delete();
        return Response::success();
    }
}
