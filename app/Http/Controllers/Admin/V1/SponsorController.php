<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SponsorCollection;
use App\Http\Resources\Admin\SponsorResource;
use App\Models\Sponsor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class SponsorController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Sponsor::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new SponsorCollection($data));
    }

    public function show(Sponsor $sponsor): JsonResponse|JsonResource
    {
        return Response::success(new SponsorResource($sponsor));
    }

    public function audit(Request $request, Sponsor $sponsor): JsonResponse|JsonResource
    {
        $request->validate([
            'status' => 'required|in:PASSED,REFUSE',
            'remark' => 'sometime|string',
        ]);
        $sponsor->update($request->all());
        return Response::success();
    }
}
