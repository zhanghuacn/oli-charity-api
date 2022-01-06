<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function abort;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request['username'])->orWhere('email', $request['username'])->first();
        if (!$admin || !Hash::check($request->input('password'), $admin->password)) {
            abort(422, 'The provided credentials are incorrect.');
        }
        return Response::success($this->getLoginInfo($admin));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->tokens()->delete();
        return Response::success();
    }

    private function getLoginInfo(Admin $admin): array
    {
        setPermissionsTeamId(0);
        $data = $admin->createPlaceToken('admin', ['place-admin']);
        $data['admin'] = [
            'id' => $admin->id,
            'avatar' => $admin->avatar,
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'roles' => $admin->getRoleNames(),
            'permissions' => $admin->getAllPermissions(),
        ];
        return $data;
    }
}
