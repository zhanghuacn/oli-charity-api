<?php

namespace App\Http\Controllers\Sponsor\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Sponsor;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use function abort;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $user = User::has('sponsors')->where('username', $request['username'])
            ->orWhere('email', $request['username'])->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            abort(422, 'The provided credentials are incorrect.');
        }
        return Response::success($this->getLoginInfo($user));
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
        abort_if($signature['type'] != Sponsor::class, 422, 'Invalid token.');
        abort_if(Carbon::parse($signature['expires'])->lt(now()), 422, 'The token has expired.');
        try {
            $user = User::findOrFail($signature['user_id'], ['id', 'name', 'avatar', 'profile']);
            DB::transaction(function () use ($user, $request) {
                $sponsor = Sponsor::create($request->except('token'));
                $sponsor->staffs()->attach($user->id);
                setPermissionsTeamId($sponsor->id);
                $user->assignRole(Role::findByName(Role::ROLE_SPONSOR_SUPER_ADMIN, Sponsor::GUARD_NAME));
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::success($this->getLoginInfo($user));
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
        $user = User::has('sponsors')->where('email', $socialite->email)
            ->orWhere('extends->' . $provider, $socialite->id)->first();
        abort_if($user == null, 403, 'Permission denied');
        if ($user->extends[$provider] == null) {
            $user->update(['extends->' . $provider => $socialite->id]);
        }
        return Response::success($this->getLoginInfo($user));
    }

    private function getLoginInfo(User $user): array
    {
        setPermissionsTeamId($user->getTeamIdFromSponsor());
        $data = $user->createPlaceToken('sponsor', ['place-sponsor']);
        $data['user'] = [
            'id' => $user->id,
            'avatar' => $user->avatar,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions(),
        ];
        return $data;
    }

    private function checkRegister(Request $request): void
    {
        $request->validate([
            'token' => 'required|string',
            'name' => 'required|string',
            'logo' => 'sometimes|url',
            'backdrop' => 'sometimes|url',
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
