<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStripeCustomer;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as Pwd;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ]);
        $account = $request->get('account');
        $password = $request->get('password');
        $response = Http::asForm()->timeout(10)->post(config('services.custom.oli_api_url') . '/login/emailLogin', [
            'email' => $account,
            'password' => $password,
        ]);
        abort_if($response['status'] != 1, 422, 'The provided credentials are incorrect.');
        $user = User::where('phone', $account)->orWhere('email', $account)->orWhere('username', $account)->first();
        if (empty($user)) {
            if (!filter_var($account, FILTER_VALIDATE_EMAIL)) {
                $account = Str::substr($account, 0, 2) != '61' ? sprintf('61%s', $account) : $account;
                $user = User::updateOrCreate([
                    'phone' => $account,
                    'password' => $password
                ]);
            } else {
                $user = User::updateOrCreate([
                    'email' => $account,
                    'password' => $password
                ]);
            }
        }
        if (!$user->hasStripeId()) {
            ProcessStripeCustomer::dispatch($user);
        }
        return Response::success(array_merge($user->createPlaceToken('api', ['place-app']), ['user' => $user->info()]));
    }

    public function loginByPhone(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'phone' => 'required|phone:AU,mobile',
            'code' => 'required|digits:4',
        ], [
            'phone.exists' => 'The phone number is not registered or disabled'
        ]);
        $key = 'phone:login:code:' . $request->get('phone');
        if (config('app.env') == 'production') {
            abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        } else {
            if ($request->get('code') != '6666') {
                abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
            }
        }
        $response = Http::asForm()->timeout(10)->post(config('services.custom.oli_api_url') . '/login/checkRegister', [
            'phone' => $request->get('phone')
        ]);
        abort_if($response['status'] != 1, 422, 'The phone number is not registered or disabled.');
        $user = User::wherePhone($request->get('phone'))->first();
        if ($user->doesntExist()) {
            $user = User::create([
                'phone' => $request->get('phone'),
                'password' => $request->get('phone')
            ]);
        }
        abort_if($user->status == User::STATUS_FROZEN, 403, 'Account has been frozen');
        if (!$user->hasStripeId()) {
            ProcessStripeCustomer::dispatch($user);
        }
        Cache::forget($key);
        return Response::success(array_merge($user->createPlaceToken('api', ['place-app']), ['user' => $user->info()]));
    }

    public function loginByEmail(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:4',
        ]);
        $key = 'email:login:code:' . $request->get('email');
        if (config('app.env') == 'production') {
            abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        } else {
            if ($request->get('code') != '8888') {
                abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
            }
        }
        $response = Http::asForm()->timeout(10)->post(config('services.custom.oli_api_url') . '/login/checkRegister', [
            'email' => $request->get('email')
        ]);
        abort_if($response['status'] != 1, 422, 'The email is not registered or disabled.');
        $user = User::whereEmail($request->get('email'))->first();
        if ($user->doesntExist()) {
            $user = User::create([
                'email' => $request->get('email'),
                'password' => $request->get('phone')
            ]);
        }
        abort_if($user->status == User::STATUS_FROZEN, 403, 'Account has been frozen');
        if (!$user->hasStripeId()) {
            ProcessStripeCustomer::dispatch($user);
        }
        Cache::forget($key);
        return Response::success(array_merge($user->createPlaceToken('api', ['place-app']), ['user' => $user->info()]));
    }

    public function socialite(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'provider' => 'required|in:GOOGLE,FACEBOOK,TWITTER,APPLE',
            'token' => 'required|string',
        ]);
        $provider = Str::lower($request->get('provider'));
        try {
            $socialite = Socialite::driver($provider)->userFromToken($request->get('token'));
        } catch (Exception $e) {
            abort($e->getCode(), $e->getMessage());
        }
        abort_if($socialite == null, 422, 'The provided credentials are incorrect.');
        $user = User::updateOrCreate(
            [
                'email' => $socialite->email,
            ],
            [
                'name' => $socialite->name,
                'avatar' => $socialite->avatar,
                'email_verified_at' => Carbon::now(),
                'extends->' . $provider => $socialite->id,
            ]
        );
        abort_if($user->status == User::STATUS_FROZEN, 403, 'Account has been frozen');
        if (!$user->hasStripeId()) {
            ProcessStripeCustomer::dispatch($user);
        }
        return Response::success(array_merge($user->createPlaceToken('api', ['place-app']), ['user' => $user->info()]));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->token()->revoke();
        return Response::success();
    }

    public function resetByEmail(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'code' => 'required|string',
            'email' => 'required|email|exists:users',
            'password' => ['required', 'confirmed', Pwd::min(8)->mixedCase()->numbers()->uncompromised()],
        ]);
        $email = $request->input('email');
        $key = 'email:login:code:' . $email;
        if (config('app.env') == 'production') {
            abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        } else {
            if ($request->get('code') != '8888') {
                abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
            }
        }
        $user = User::whereEmail($email)->firstOrFail();
        abort_if($user->status == User::STATUS_FROZEN, 403, 'Account has been frozen');
        $user->forceFill(['password' => Hash::make($request->get('password')),])->save();
        $user->tokens()->delete();
        if (!$user->hasStripeId()) {
            ProcessStripeCustomer::dispatch($user);
        }
        Cache::forget($key);
        return Response::success();
    }

    public function resetByPhone(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'code' => 'required|string',
            'phone' => 'required|phone:AU,mobile|exists:users',
            'password' => ['required', 'confirmed', Pwd::min(8)->mixedCase()->numbers()->uncompromised()],
        ], [
            'phone.exists' => 'The phone number is not registered or disabled'
        ]);
        $phone = $request->input('phone');
        $key = 'phone:login:code:' . $phone;
        if (config('app.env') == 'production') {
            abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        } else {
            if ($request->get('code') != '6666') {
                abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
            }
        }
        $user = User::wherePhone($phone)->firstOrFail();
        abort_if($user->status == User::STATUS_FROZEN, 403, 'Account has been frozen');
        $user->forceFill(['password' => Hash::make($request->get('password'))])->save();
        $user->tokens()->delete();
        if (!$user->hasStripeId()) {
            ProcessStripeCustomer::dispatch($user);
        }
        Cache::forget($key);
        return Response::success();
    }

    public function callbackSignWithOliView(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'email' => 'nullable|email|exists:users',
            'phone' => 'nullable|phone:AU,mobile|exists:users',
            'token' => 'required|string',
        ], [
            'phone.exists' => 'The phone number is not registered or disabled'
        ]);
        $email = $request->get('email');
        $phone = $request->get('phone');
        if (!empty($email)) {
            abort_if($request->get('token') != md5($email), 422, 'Parameter request error');
            User::whereEmail($email)->update(['sync' => true]);
        } elseif (!empty($phone)) {
            abort_if($request->get('token') != md5($phone), 422, 'Parameter request error');
            User::wherePhone($phone)->update(['sync' => true]);
        } else {
            abort(422, '邮箱或手机不能为空');
        }
        return Response::success();
    }
}
