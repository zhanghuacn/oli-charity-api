<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessRegOliView;
use App\Models\User;
use AWS;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as Pwd;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;
use function abort;
use function abort_if;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
//            'code' => 'required|numeric',
            'password' => ['required', Pwd::min(8)->mixedCase()->numbers()->uncompromised()],
        ]);
//        $key = 'email:register:code:' . $request->get('email');
//        abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        $user = User::create($request->all());
        ProcessRegOliView::dispatch($request->all());
        return Response::success($this->getLoginInfo($user));
    }

    public function login(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $user = User::where('username', $request['username'])->orWhere('email', $request['username'])->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            abort(422, 'The provided credentials are incorrect.');
        }
        return Response::success($this->getLoginInfo($user));
    }

    public function loginByPhone(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|digits:6',
        ]);
        $key = 'phone:login:code:' . $request->get('phone');
        abort_if($request->get('code') != Cache::get($key), '422', "Verification code error");
        $user = User::where('phone', $request['username'])->first();
        return Response::success($this->getLoginInfo($user));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->token()->revoke();
        return Response::success();
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
        $user = User::where('email', $socialite->email)
            ->orWhere('extends->' . $provider, $socialite->id)->first();
        if ($user == null) {
            $user = User::create([
                'email' => $socialite->email,
                'username' => $socialite->email,
                'name' => $socialite->name,
                'avatar' => $socialite->avatar,
                'email_verified_at' => Carbon::now()->tz(config('app.timezone')),
                'extends->' . $provider => $socialite->id,
            ]);
        }
        if ($user->extends[$provider] == null) {
            $user->update(['extends->' . $provider => $socialite->id]);
        }
        return Response::success($this->getLoginInfo($user));
    }

    private function getLoginInfo(User $user): array
    {
        $data = $user->createPlaceToken('api', ['place-app']);
        $data['user'] = [
            'id' => $user->id,
            'avatar' => $user->avatar,
            'name' => $user->name,
            'backdrop' => $user->backdrop,
            'profile' => $user->profile,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'gender' => $user->gender,
            'birthday' => Carbon::parse($user->birthday)->tz(config('app.timezone'))->toDateString(),
            'is_public_records' => $user->extends['records'],
            'is_public_portfolio' => $user->extends['portfolio'],
            'type' => $user->charities()->exists() ? 'CHARITY' : ($user->sponsors()->exists() ? 'SPONSOR' : 'USER'),
            'type_name' => $user->charities()->exists() ? $user->charities()->first()->name : ($user->sponsors()->exists() ? $user->sponsors()->first()->name : ''),
        ];
        return $data;
    }

    public function sendRegisterCodeEmail(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        abort_if(User::whereEmail($request->get('email'))->exists(), 422, 'Email registered');
        $code = rand(100000, 999999);
        $email = $request->get('email');
        $key = 'email:register:code:' . $email;//redis key
        Cache::put($key, $code, Carbon::now()->tz(config('app.timezone'))->addMinutes(15));
        Mail::send('mail.SendEmailCode', ['code' => $code, 'operation' => 'register', 'email' => $email], function (Message $message) use ($email) {
            $message->to($email);
            $message->subject('Imagine 2080 Email verification');
        });
        if (Mail::failures()) {
            return Response::fail('fail in send');
        }
        return Response::success();
    }

    public function sendLoginCodePhone(Request $request): JsonResponse|JsonResource
    {
//        $request->validate([
//            'phone' => 'required|string',
//        ]);
//        try {
//            $code = rand(100000, 999999);
//            $phone = $request->get('phone');
//            $key = 'phone:login:code:' . $phone;
//            Cache::put($key, $code, Carbon::now()->tz(config('app.timezone'))->addMinutes(15));
//            $sms = AWS::createClient('sns');
//            $sms->publish([
//                'Message' => 'Hello, This is just a test Message',
//                'PhoneNumber' => $phone,
//                'MessageAttributes' => [
//                    'AWS.SNS.SMS.SMSType' => [
//                        'DataType' => 'String',
//                        'StringValue' => 'Transactional',
//                    ]
//                ],
//            ]);
//        } catch (Exception $e) {
//            abort(500, $e->getMessage());
//        }
        return Response::success();
    }

    public function sendForgotCodeEmail(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $code = rand(100000, 999999);
        $email = $request->get('email');
        $key = 'email:forgot:code:' . $request->get('email');//redis key
        Cache::put($key, $code, Carbon::now()->tz(config('app.timezone'))->addMinutes(15));
        Mail::send('mail.SendEmailCode', ['code' => $code, 'operation' => 'forgot password', 'email' => $email], function (Message $message) use ($email) {
            $message->to($email);
            $message->subject('Imagine 2080 Email verification');
        });
        if (Mail::failures()) {
            return Response::fail('fail in send');
        }
        return Response::success();
    }

    public function reset(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'code' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Pwd::min(8)->mixedCase()->numbers()->uncompromised()],
        ]);
        $email = $request->input('email');
        $code = $request->input('code');
        $key = 'email:forgot:code:' . $email;
        $value = Cache::get($key);
        if ($value && $value == $code) {
            $user = User::whereEmail($email)->firstOrFail();
            $user->forceFill([
                'password' => Hash::make($request->get('password')),
            ])->save();
            $user->tokens()->delete();
            event(new PasswordReset($user));
            Cache::delete($key);
            return Response::success();
        } else {
            return Response::fail('Verification code error');
        }
    }

    public function callbackSignWithOliView(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);
        abort_if($request->get('token') != md5($request->get('email')), 422, 'Parameter request error');
        User::whereEmail($request->get('email'))->update(['sync' => true]);
        return Response::success();
    }
}
