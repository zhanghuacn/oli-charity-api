<?php

namespace App\Http\Controllers\Sponsor\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jiannei\Response\Laravel\Support\Facades\Response;
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
        return Response::success($user->createPlaceToken('sponsor', ['place-sponsor']));
    }

    public function logout(Request $request): JsonResponse|JsonResource
    {
        $request->user()->tokens()->delete();
        return Response::success();
    }
}
