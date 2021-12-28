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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Spatie\Permission\PermissionRegistrar;
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
            'documents' => 'required|array',
            'contact' => 'required|string',
            'phone' => 'required|string',
            'mobile' => 'required|string',
            'email' => 'required|email',
            'address' => 'required|string',
        ]);
        $signature = json_decode(Crypt::decryptString($request->get('token')), true);
        if (Carbon::parse($signature['expires'])->lt(now())) {
            abort(422, 'The token has expired.');
        }
        try {
            $user = DB::transaction(function () use ($request, $signature) {
                $user = User::findOrFail($signature['user_id']);
                $charity = Charity::create($request->except('token'));
                $charity->staffs()->attach($user->id);
                setPermissionsTeamId($charity->id);
                $role = Role::create(['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_CHARITY_SUPER_ADMIN]);
                Role::create(['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_CHARITY_ADMIN]);
                Role::create(['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_CHARITY_STAFF]);
                $user->assignRole($role);
                return $user;
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::success($user->createPlaceToken('charity', ['place-charity']));
    }
}
