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
use Laravel\Socialite\Facades\Socialite;

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
        return Response::success($user->createDeviceToken($request->input('device_name')));
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

        $user = User::where('username', $request->input('username'))->orWhere('email', $request->input('username'))->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        return Response::success($user->createDeviceToken($request->input('device_name')));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->tokens()->delete();
        return Response::success();
    }

    public function socialite(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'driver' => 'required|in:GOOGLE,FACEBOOK,TWITTER,APPLE',
            'token' => 'required',
        ]);
        $social_user = Socialite::driver($request->input('driver'))->userFromToken($request->input('token'));
        abort_if($social_user == null, 400, 'Invalid credentials');
        return Response::success();
    }
}
