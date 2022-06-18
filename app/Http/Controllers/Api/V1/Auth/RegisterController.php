<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password as Pwd;
use Jiannei\Response\Laravel\Support\Facades\Response;

class RegisterController extends Controller
{
    public function registerEmail(Request $request): JsonResponse|JsonResource
    {
        $response = Http::asForm()->timeout(10)->post(config('services.custom.oli_api_url') . '/login/checkRegister', [
            'email' => $request->get('email')
        ]);
        abort_if(!empty($response['data']), 422, 'The Email is registered.');
        $request->validate([
            'email' => 'required|email|unique:users',
            'code' => 'required|digits:4',
            'password' => ['required', Pwd::min(8)],
        ], [
            'email.unique' => 'The email is not registered or disabled',
        ]);
        $key = 'email:register:code:' . $request->get('email');
        if (config('app.env') == 'production') {
            abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        } else {
            if ($request->get('code') != '8888') {
                abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
            }
        }
        $user = User::create($request->only(['email', 'password']));
        return Response::success(array_merge($user->createPlaceToken('api', ['place-app']), ['user' => $user->info()]));
    }

    public function registerPhone(Request $request): JsonResponse|JsonResource
    {
        $response = Http::asForm()->timeout(10)->post(config('services.custom.oli_api_url') . '/login/checkRegister', [
            'phone' => $request->get('phone')
        ]);
        abort_if(!empty($response['data']), 422, 'The phone is registered.');
        $request->validate([
            'phone' => 'required|phone:AU,mobile|unique:users',
            'code' => 'required|digits:4',
            'password' => ['required', Pwd::min(8)],
        ], [
            'phone.unique' => 'The phone number is not registered or disabled'
        ]);
        $key = 'phone:register:code:' . $request->get('phone');
        if (config('app.env') == 'production') {
            abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        } else {
            if ($request->get('code') != '6666') {
                abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
            }
        }
        $user = User::create($request->only(['phone', 'password']));
        return Response::success(array_merge($user->createPlaceToken('api', ['place-app']), ['user' => $user->info()]));
    }
}
