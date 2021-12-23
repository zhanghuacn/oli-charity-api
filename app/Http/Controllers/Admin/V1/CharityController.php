<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Models\Charity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class CharityController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $data = Charity::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success($data);
    }

    public function show(Charity $charity): JsonResponse|JsonResource
    {
        return Response::success($charity);
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
