<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;


class UCenterController extends Controller
{
    public function notifications(Request $request): JsonResponse|JsonResource
    {
        return Response::success(Auth::user()->notifications);
    }

    public function information(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'avatar' => 'sometimes|url',
            'first_name' => 'sometimes|string',
            'middle_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'birthday' => 'sometimes|date',
            'name' => 'sometimes|string',
            'profile' => 'sometimes|string',
        ]);
        Auth::user()->update($request->only(['avatar', 'first_name', 'middle_name', 'last_name', 'birthday', 'name', 'profile']));
        Auth::user()->refresh();
        return Response::success();
    }

    public function privacy(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'portfolio' => 'required|boolean',
            'records' => 'required|boolean',
        ]);
        Auth::user()->update(['settings->portfolio' => $request['portfolio'], 'settings->records' => $request['records']]);
        Auth::user()->refresh();
        return Response::success(Auth::user()->settings);
    }
}
