<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Oauth;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
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
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->uncompromised()],
        ]);
        $user = User::create($request->all());
        Event::dispatch(new Registered($user));
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

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->token()->revoke();
        return Response::success();
    }

    public function socialite(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'provider' => 'required|in:GOOGLE,FACEBOOK,TWITTER',
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
                'email_verified_at' => now(),
                'extends->' . $provider => $socialite->id,
            ]);
        }
        if ($user->extends[$provider] == null) {
            $user->update(['extends->' . $provider => $socialite->id]);
        }
        return Response::success($this->getLoginInfo($user));
    }

    public function verifyEmail(Request $request): Redirector|string|RedirectResponse|Application
    {
        $user = User::find($request->route('id'));
        if ($user->hasVerifiedEmail()) {
            return 'Mailbox verified';
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        return redirect('https://www.qq.com');
    }

    public function resend(Request $request): JsonResponse|JsonResource
    {
        $request->user()->sendEmailVerificationNotification();
        return Response::success();
    }

    private function getLoginInfo($user): array
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
            'birthday' => $user->birthday,
            'is_public_records' => $user->extends['records'],
            'is_public_portfolio' => $user->extends['portfolio'],
        ];
        return $data;
    }
}
