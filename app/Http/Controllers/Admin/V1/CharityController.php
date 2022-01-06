<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CharityCollection;
use App\Http\Resources\Admin\CharityResource;
use App\Models\Charity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class CharityController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Charity::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new CharityCollection($data));
    }

    public function show(Charity $charity): JsonResponse|JsonResource
    {
        return Response::success(new CharityResource($charity));
    }

    public function audit(Request $request, Charity $charity): JsonResponse|JsonResource
    {
        $request->validate([
            'status' => 'required|in:PASSED,REFUSE',
            'remark' => 'sometime|string',
        ]);
        $charity->update($request->all());
        return Response::success();
    }
}
