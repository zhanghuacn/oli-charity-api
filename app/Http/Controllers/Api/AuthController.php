<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Facades\Response;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);
        $user = User::create($request->all());
        return Response::success($user->createDeviceToken($request->get('device_name')));
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->orWhere('email', $request->username)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }
        return Response::success($user->createDeviceToken($request->get('device_name')));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->tokens()->delete();
        return Response::ok();
    }
}
