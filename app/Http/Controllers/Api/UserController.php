<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class UserController extends Controller
{
    public function show(User $user): JsonResponse|JsonResource
    {
        if (Auth::check()) {
            $user = $user->attachFollowStatus(Auth::user());
        }
        return Response::success(new UserResource($user));
    }

    public function follow(User $user): JsonResponse|JsonResource
    {
        Auth::user()->follow($user);
        return Response::success();
    }

    public function unfollow(User $user): JsonResponse|JsonResource
    {
        Auth::user()->unfollow($user);
        return Response::success();
    }
}
