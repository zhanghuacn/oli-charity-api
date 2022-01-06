<?php

namespace App\Http\Controllers\Sponsor\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sponsor\SponsorResource;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class SponsorController extends Controller
{
    public function show(): JsonResponse|JsonResource
    {
        $charity = Sponsor::findOrFail(getPermissionsTeamId());
        return Response::success(new SponsorResource($charity));
    }

    public function update(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'logo' => 'sometimes|url',
            'backdrop' => 'sometimes|url',
            'website' => 'sometimes|url',
            'description' => 'sometimes|string',
            'introduce' => 'sometimes|string',
        ]);
        Sponsor::findOrFail(getPermissionsTeamId())->update([
            'logo' => $request->get('logo'),
            'backdrop' => $request->get('backdrop'),
            'website' => $request->get('website'),
            'description' => $request->get('description'),
            'introduce' => $request->get('introduce'),
        ]);
        return Response::success();
    }
}
