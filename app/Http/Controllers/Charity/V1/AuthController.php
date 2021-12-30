<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Models\Charity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use function abort;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $user = User::has('charities')->where('username', $request['username'])
            ->orWhere('email', $request['username'])->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            abort(422, 'The provided credentials are incorrect.');
        }
        return Response::success($user->createPlaceToken('charity', ['place-charity']));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->tokens()->delete();
        return Response::success();
    }

    public function register(Request $request): JsonResponse|JsonResource
    {
        $this->checkRegister($request);
        $signature = json_decode(Crypt::decryptString($request->get('token')), true);
        abort_if(Carbon::parse($signature['expires'])->lt(now()), 422, 'The token has expired.');
        try {
            $user = User::findOrFail($signature['user_id'], ['id', 'name', 'avatar', 'profile']);
            DB::transaction(function () use ($user, $request, $signature) {
                $charity = Charity::create($request->except('token'));
                $charity->staffs()->attach($user->id);
                setPermissionsTeamId($charity->id);
                $user->assignRole(Role::findByName(Role::ROLE_CHARITY_SUPER_ADMIN, Charity::GUARD_NAME));
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::success($user->createPlaceToken('charity', ['place-charity']));
    }

    public function socialite(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'provider' => 'required|in:GOOGLE,FACEBOOK,TWITTER,APPLE',
            'access_token' => 'required|string',
        ]);
        $socialite = Socialite::driver($request->get('provider'))->userFromToken($request->get('access_token'));
        abort_if($socialite == null, 422, 'The provided credentials are incorrect.');
        $user = User::has('charities')->where('username', $socialite->email)
            ->orWhere('email', $socialite->email)->first();
        abort_if($user == null, 403, 'Permission denied');
        return Response::success($user->createPlaceToken('charity', ['place-charity']));
    }

    private function checkRegister(Request $request): void
    {
        $request->validate([
            'token' => 'required|string',
            'name' => 'required|string',
            'logo' => 'required|url',
            'backdrop' => 'required|url',
            'website' => 'required|url',
            'description' => 'required|string',
            'introduce' => 'required|string',
            'staff_num' => 'required|numeric|min:0',
            'credentials' => 'required|array',
            'credentials.*' => 'required|url',
            'documents' => 'required|array',
            'documents.*' => 'required|url',
            'contact' => 'required|string',
            'phone' => 'required|string',
            'mobile' => 'required|string',
            'email' => 'required|email',
            'address' => 'required|string',
        ]);
    }
}
