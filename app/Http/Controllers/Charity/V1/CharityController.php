<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Charity\CharityResource;
use App\Models\Charity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class CharityController extends Controller
{
    public function show(): JsonResponse|JsonResource
    {
        $charity = Charity::findOrFail(getPermissionsTeamId());
        return Response::success(new CharityResource($charity));
    }

    public function update(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'logo' => 'sometimes|url',
            'backdrop' => 'sometimes|url',
            'website' => 'sometimes|url',
            'description' => 'sometimes|string',
            'introduce' => 'sometimes|string',
            'card' => 'sometimes|array',
            'card.*.account_name' => 'required|string',
            'card.*.bank_name' => 'required|string',
            'card.*.account_no' => 'required|string',
            'card.*.bsb' => 'required|string',
            'card.*.swift_code' => 'required|string',
        ]);
        Charity::findOrFail(getPermissionsTeamId())->update([
            'logo' => $request->get('logo'),
            'backdrop' => $request->get('backdrop'),
            'website' => $request->get('website'),
            'description' => $request->get('description'),
            'introduce' => $request->get('introduce'),
            'extends->cards' => $request->get('cards')
        ]);
        return Response::success();
    }
}
