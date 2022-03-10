<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password as Pwd;
use Jiannei\Response\Laravel\Support\Facades\Response;

class RegisterController extends Controller
{
    public function registerEmail(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'code' => 'required|digits:6',
            'password' => ['required', Pwd::min(8)->mixedCase()->numbers()->uncompromised()],
        ]);
        $key = 'email:register:code:' . $request->get('email');
        if (config('app.env') == 'production') {
            abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        } else {
            if ($request->get('code') != '888888') {
                abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
            }
        }
        $user = User::create($request->only(['email', 'password']));
        return Response::success($user->userInfo());
    }

    public function registerPhone(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'phone' => 'required|phone:AU,mobile|unique:users',
            'code' => 'required|digits:6',
            'password' => ['required', Pwd::min(8)->mixedCase()->numbers()->uncompromised()],
        ]);
        $key = 'phone:register:code:' . $request->get('phone');
        if (config('app.env') == 'production') {
            abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        } else {
            if ($request->get('code') != '666666') {
                abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
            }
        }
        $user = User::create($request->only(['phone', 'password']));
        return Response::success($user->userInfo());
    }
}
