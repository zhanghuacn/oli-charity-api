<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
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
            'keyword' => 'sometimes|string',
        ]);
        $sponsors = Sponsor::filter($request->all())->limit(10)->get(['id', 'name', 'logo']);
        return Response::success($sponsors);
    }
}
