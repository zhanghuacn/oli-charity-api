<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\CharityCollection;
use App\Http\Resources\Api\CharityResource;
use App\Models\Charity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class CharityController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $paginate = Charity::orderByDesc('id')->simplePaginate($request->input('per_page', 15));
        return Response::success(new CharityCollection($paginate));
    }

    public function show(Charity $charity): JsonResponse|JsonResource
    {
        visits($charity)->increment();
        return Response::success(new CharityResource($charity));
    }

    public function activities(Charity $charity): JsonResponse|JsonResource
    {
        return Response::success(new ActivityCollection($charity->activities));
    }

    public function subscribe(Charity $charity): JsonResponse|JsonResource
    {
        Auth::user()->subscribe($charity);
        return Response::success();
    }

    public function unsubscribe(Charity $charity): JsonResponse|JsonResource
    {
        try {
            Auth::user()->unsubscribe($charity);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }
}
