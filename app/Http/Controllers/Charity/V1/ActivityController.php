<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        Gate::authorize('viewAny');
        return Response::success();
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        return Response::success();
    }

    public function show($id): JsonResponse|JsonResource
    {
        return Response::success();
    }

    public function update(Request $request, $id): JsonResponse|JsonResource
    {
        return Response::success();
    }

    public function destroy($id): JsonResponse|JsonResource
    {
        return Response::success();
    }
}
