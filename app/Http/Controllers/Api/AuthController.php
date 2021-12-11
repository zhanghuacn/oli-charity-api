<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oauth;
use App\Models\User;
use App\Models\UserSocialite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;
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

    public function login(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request['username'])->orWhere('email', $request['username'])->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            abort(422, 'The provided credentials are incorrect.');
        }
        return Response::success($user->createDeviceToken('api', ['role:api']));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->tokens()->delete();
        return Response::success();
    }

    public function socialiteLogin(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'driver' => 'required|in:GOOGLE,FACEBOOK,TWITTER,APPLE',
            'token' => 'required',
        ]);
        $social_user = Socialite::driver($request['driver'])->userFromToken($request['token']);
        abort_if($social_user == null, 400, 'Invalid credentials');
        $oauth = Oauth::with('user')->where([
            ['provider', '=', $request['driver']],
            ['provider_id', '=', $social_user->id],
        ])->firstOrFail();
        return Response::success($oauth->user()->createDeviceToken('api', ['role:api']));
    }

    public function socialiteBind(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'driver' => 'required|in:GOOGLE,FACEBOOK,TWITTER,APPLE',
            'token' => 'required',
        ]);
        $social_user = Socialite::driver($request['driver'])->userFromToken($request['token']);
        abort_if($social_user == null, 400, 'Invalid credentials');
        $user = User::where('username', $request['username'])->orWhere('email', $request['username'])->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            abort(422, 'The provided credentials are incorrect.');
        }
        $user->oauths()->save(new Oauth([
            'provider' => $request['driver'],
            'provider_id' => $social_user->id,
        ]));
        return Response::success($user->createDeviceToken('api', ['role:api']));
    }

    public function socialiteRegister(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'driver' => 'required|in:GOOGLE,FACEBOOK,TWITTER,APPLE',
            'token' => 'required',
        ]);

        $social_user = Socialite::driver($request['driver'])->userFromToken($request['token']);
        abort_if($social_user == null, 400, 'Invalid credentials');
        $user = User::create([
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => $request['password'],
        ]);
        $user->oauths()->create([
            'provider' => $request['driver'],
            'provider_id' => $social_user->id,
        ]);
        $user->refresh();
        return Response::success($user->createDeviceToken('api', ['role:api']));
    }
}
