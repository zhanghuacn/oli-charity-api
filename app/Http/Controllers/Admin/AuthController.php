<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;
use Jiannei\Response\Laravel\Support\Facades\Response;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $admin = Admin::where('username', $request['username'])->orWhere('email', $request['username'])->first();
        if (!$admin || !Hash::check($request->input('password'), $admin->password)) {
            abort(422, 'The provided credentials are incorrect.');
        }
        return Response::success($admin->createDeviceToken('admin', ['place-admin']));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->tokens()->delete();
        return Response::success();
    }
}
